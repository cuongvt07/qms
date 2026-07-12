<?php

namespace App\Livewire;

use App\Models\FormSubmission;
use App\Models\FormSubmissionAttachment;
use App\Models\FormSubmissionRow;
use App\Models\FormTemplateVersion;
use App\Services\ActivityLogger;
use App\Services\TableStructureService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Nhập biểu mẫu theo NGÀY dạng TAB:
 *   - Mỗi tab = 1 ngày = 1 form_submission.
 *   - Mỗi tab hiển thị nguyên phiếu dọc theo bản gốc (điền/tích/chọn/bảng).
 *   - Thêm ngày = thêm tab.
 */
class RegisterFill extends Component
{
    use WithFileUploads;

    public int    $versionId  = 0;
    public array  $rows       = [];   // [['id','ngay','data'=>[k=>v],'tables'=>[fk=>[[c=>v]]],'trang_thai'], ...]
    public array  $deletedIds = [];
    public string $thang      = '';
    public int    $active     = 0;
    public string $title      = '';

    // Sao chép dữ liệu 1 ngày sang nhiều ngày (chọn ngày)
    public bool   $showCopy   = false;
    public string $copyMonth  = '';
    public array  $copyDates  = [];

    // Tệp/ảnh đính kèm cho bản lưu (submission) của ngày đang chọn
    public $uploads = [];

    // Tự động lưu
    public ?string $savedAt = null;

    public function mount(int $versionId): void
    {
        $this->versionId = $versionId;
        $this->thang     = now()->format('Y-m');
        $this->title     = $this->version->formTemplate->ten_bm;

        $subs = FormSubmission::where('form_template_version_id', $versionId)
            ->where('user_id', auth()->id())
            ->orderBy('ngay_nhap', 'desc')
            ->get();

        $this->rows = $subs->map(function ($s) {
            $tables = [];
            foreach ($this->tableFields as $tf) {
                $trs = FormSubmissionRow::where('form_submission_id', $s->id)
                    ->where('field_key', $tf['key'])->orderBy('row_index')->get();
                $tables[$tf['key']] = $trs->map(fn ($r) => $r->row_data_json)->all();
            }
            return [
                'id' => $s->id, 'ngay' => Carbon::parse($s->ngay_nhap)->toDateString(),
                'data' => $s->data_json ?? [], 'tables' => $tables, 'trang_thai' => $s->trang_thai,
                'attachments' => $this->attList($s->id),
            ];
        })->all();

        if (empty($this->rows)) {
            $this->addDay(now()->toDateString());
        }
        $this->active = 0;

        // Mở đúng ngày khi bấm từ lịch (?date=YYYY-MM-DD)
        $focus = request('date');
        if ($focus) {
            $idx = array_search($focus, array_column($this->rows, 'ngay'), true);
            if ($idx !== false) {
                $this->active = (int) $idx;
            } else {
                $this->addDay($focus);   // addDay tự set active
            }
            $this->thang = Carbon::parse($focus)->format('Y-m');
        }
    }

    public function getVersionProperty(): FormTemplateVersion
    {
        return FormTemplateVersion::with('formTemplate')->findOrFail($this->versionId);
    }

    /**
     * Field hiển thị (bỏ field ẩn), XẾP THEO THỨ TỰ TRONG FILE GỐC (trên→dưới), không phải ABC.
     * Lọc ${...} lẫn trong NHÃN (không đụng option text = khoá map export).
     */
    public function getFieldsProperty(): array
    {
        // bắt cả placeholder đủ ${x} lẫn mảnh cụt ${chk_giao_t (injector cắt nhãn giữa chừng)
        $clean = fn ($s) => trim(preg_replace('/\$\{[a-z0-9_]*\}?/i', '', (string) $s));
        $order = $this->placeholderOrder();
        $posOf = function ($f) use ($order) {
            $keys = ($f['type'] ?? '') === 'repeatable_table'
                ? array_column($f['columns'] ?? [], 'key')
                : (! empty($f['option_ph']) ? array_values($f['option_ph']) : [$f['key'] ?? '']);
            $p = PHP_INT_MAX;
            foreach ($keys as $k) {
                if (isset($order[$k])) {
                    $p = min($p, $order[$k]);
                }
            }
            return $p;
        };
        // Đồng bộ với "Cấu hình ẩn ô" ở màn Giống bản gốc: ô ẩn ở bản gốc thì phiếu cũng ẩn.
        $hiddenSet = array_flip($this->version->schema_json['inline_hidden'] ?? []);
        $isHidden  = function ($f) use ($hiddenSet) {
            if (! empty($f['hidden'])) {
                return true;
            }
            if (isset($hiddenSet[$f['key'] ?? ''])) {
                return true;
            }
            if (! empty($f['option_ph'])) {   // nhóm chọn: ẩn khi MỌI lựa chọn đều bị ẩn
                foreach (array_values($f['option_ph']) as $ph) {
                    if (! isset($hiddenSet[$ph])) {
                        return false;
                    }
                }
                return true;
            }
            return false;
        };
        $fields = array_values(array_map(function ($f) use ($clean) {
            $f['label'] = $clean($f['label'] ?? '');
            if (! empty($f['columns'])) {
                $f['columns'] = array_map(function ($c) use ($clean) {
                    $c['label'] = $clean($c['label'] ?? '');
                    return $c;
                }, $f['columns']);
            }
            return $f;
        }, array_filter($this->version->fields, fn ($f) => ! $isHidden($f))));
        usort($fields, fn ($a, $b) => $posOf($a) <=> $posOf($b));   // PHP 8 usort ổn định
        return $fields;
    }

    /**
     * Gộp các ô ĐƠN GIẢN (text/number/date) LIÊN TIẾP TRÙNG NHÃN thành 1 nhóm.
     * Bảng chấm điểm bị làm phẳng (Giá cả ×4, Tiến độ ×4…) sẽ hiện gọn: nhãn 1 lần + các ô xếp ngang có đánh số.
     * Trả về list: ['kind'=>'group','label'=>..,'items'=>[field,...]] hoặc ['kind'=>'single','field'=>field].
     */
    public function getFieldGroupsProperty(): array
    {
        $simple = ['text', 'number', 'date'];
        // Nhãn gốc: bỏ số thứ tự ở cuối ("Cell 2"->"Cell", "Giá cả"->"Giá cả") để gộp cùng 1 cụm.
        $base = fn ($lbl) => trim(preg_replace('/[\s_\-.]*\d+$/u', '', (string) $lbl));
        // Nhãn rác (ô trống trong bảng injector đặt tên chung) -> đổi tên cho dễ hiểu.
        $isJunk = fn ($b) => (bool) preg_match('/^(cell|cells|o|ô|cot|cột|col|column)$/iu', trim($b));
        $BIG    = 12;   // nhóm > 12 ô = bảng lớn -> thu gọn, khuyến nghị Giống bản gốc

        $fields = $this->fields;
        $n      = count($fields);
        $out    = [];
        $i      = 0;
        while ($i < $n) {
            $f    = $fields[$i];
            $type = $f['type'] ?? 'text';
            $b    = $base(trim((string) ($f['label'] ?? '')));
            if ($b !== '' && in_array($type, $simple, true)) {
                $run = [$f];
                $j   = $i + 1;
                while ($j < $n
                    && in_array($fields[$j]['type'] ?? 'text', $simple, true)
                    && $base(trim((string) ($fields[$j]['label'] ?? ''))) === $b) {
                    $run[] = $fields[$j];
                    $j++;
                }
                if (count($run) >= 2) {
                    $out[] = [
                        'kind'  => 'group',
                        'label' => $isJunk($b) ? 'Ô trong bảng' : $b,
                        'items' => $run,
                        'big'   => count($run) > $BIG,
                    ];
                    $i = $j;
                    continue;
                }
            }
            $out[] = ['kind' => 'single', 'field' => $f];
            $i++;
        }
        return $out;
    }

    /** key => field (nhãn đã làm sạch) để dựng ô nhập trong bảng. */
    public function getFieldMapProperty(): array
    {
        $m = [];
        foreach ($this->fields as $f) {
            if (! empty($f['key'])) {
                $m[$f['key']] = $f;
            }
        }
        return $m;
    }

    /**
     * Kế hoạch render Dạng phiếu theo đúng thứ tự bản gốc:
     *  - 'table'  : bảng lưới thật (≥3 cột + có hàng tiêu đề) dựng lại từ .docx (giữ gộp cột/hàng, tiêu đề chuẩn).
     *  - 'group'  : các ô cùng tên gộp lại (bảng làm phẳng không nhận dạng được lưới).
     *  - 'single' : ô đơn.
     * Trả ['plan'=>[...], 'tables'=>[...]].
     */
    public function getRenderPlanProperty(): array
    {
        $svc    = app(TableStructureService::class)->forVersion($this->version);
        $tables = $svc['tables'];
        $k2t    = $svc['keyToTable'];

        // Chỉ dựng lại bảng LƯỚI THẬT: có ≥1 hàng tiêu đề, ≥3 cột, ≥1 hàng dữ liệu.
        $renderable = [];
        foreach ($tables as $ti => $T) {
            $dataRows = count($T['rows']) - $T['headerRows'];
            $renderable[$ti] = ($T['headerRows'] >= 1 && $T['gridW'] >= 3 && $dataRows >= 1);
        }
        $inTable = function ($key) use ($k2t, $renderable) {
            return isset($k2t[$key]) && ! empty($renderable[$k2t[$key]]);
        };

        $simple = ['text', 'number', 'date'];
        $base   = fn ($l) => trim(preg_replace('/[\s_\-.]*\d+$/u', '', (string) $l));
        $isJunk = fn ($b) => (bool) preg_match('/^(cell|cells|o|ô|cot|cột|col|column)$/iu', trim($b));
        $BIG    = 12;

        $fields  = $this->fields;
        $n       = count($fields);
        $plan    = [];
        $emitted = [];
        $i       = 0;
        while ($i < $n) {
            $f   = $fields[$i];
            $key = $f['key'] ?? '';
            if ($inTable($key)) {
                $ti = $k2t[$key];
                if (empty($emitted[$ti])) {
                    $emitted[$ti] = true;
                    $plan[] = ['kind' => 'table', 'idx' => $ti];
                }
                $i++;
                continue;
            }
            // Gom các ô NGÀY/THÁNG/NĂM (kể cả ngày đầy đủ) LIÊN TIẾP -> 1 "dòng ngày" hiển thị liền mạch.
            if (self::dateKind($f) !== null) {
                $run = [$f];
                $j   = $i + 1;
                while ($j < $n && ! $inTable($fields[$j]['key'] ?? '') && self::dateKind($fields[$j]) !== null) {
                    $run[] = $fields[$j];
                    $j++;
                }
                if (count($run) >= 2) {
                    $plan[] = ['kind' => 'dateline', 'items' => $run];
                    $i = $j;
                    continue;
                }
            }
            $type = $f['type'] ?? 'text';
            $b    = $base(trim((string) ($f['label'] ?? '')));
            if ($b !== '' && in_array($type, $simple, true)) {
                $run = [$f];
                $j   = $i + 1;
                while ($j < $n
                    && ! $inTable($fields[$j]['key'] ?? '')
                    && in_array($fields[$j]['type'] ?? 'text', $simple, true)
                    && $base(trim((string) ($fields[$j]['label'] ?? ''))) === $b) {
                    $run[] = $fields[$j];
                    $j++;
                }
                if (count($run) >= 2) {
                    $plan[] = ['kind' => 'group', 'label' => $isJunk($b) ? 'Ô trong bảng' : $b, 'items' => $run, 'big' => count($run) > $BIG];
                    $i = $j;
                    continue;
                }
            }
            $plan[] = ['kind' => 'single', 'field' => $f];
            $i++;
        }
        return ['plan' => $plan, 'tables' => $tables];
    }

    /** Vị trí placeholder theo thứ tự xuất hiện trong .docx gốc (key => index). Cache theo version. */
    private function placeholderOrder(): array
    {
        static $cache = [];
        $vid = $this->versionId;
        if (isset($cache[$vid])) {
            return $cache[$vid];
        }
        $order = [];
        try {
            // Đọc bản đã chuẩn hoá + đã CHÈN Ô THÊM (added_inline) -> ô thêm ở bản gốc nằm ĐÚNG vị trí bên phiếu.
            $path = app(\App\Services\InlineDocxService::class)->templatePathFor($this->version);
            $tp   = new \PhpOffice\PhpWord\TemplateProcessor($path);
            foreach (array_values($tp->getVariables()) as $i => $k) {
                if (! isset($order[$k])) {
                    $order[$k] = $i;
                }
            }
        } catch (\Throwable $e) {
            // không đọc được -> giữ thứ tự schema
        }
        return $cache[$vid] = $order;
    }

    public function getTableFieldsProperty(): array
    {
        return array_values(array_filter($this->version->fields, fn ($f) => ($f['type'] ?? '') === 'repeatable_table'));
    }

    /** Loại ô ngày tháng năm (điền tay): 'day'|'month'|'year'|'vndate'|null. Theo NHÃN (đuôi ngày/tháng/năm) rồi tới type=date. */
    public static function dateKind(array $f): ?string
    {
        $lbl = mb_strtolower(trim(preg_replace('/\$\{[^}]*\}/', '', (string) ($f['label'] ?? ''))), 'UTF-8');
        if (preg_match('/ngày$/u', $lbl)) {
            return 'day';
        }
        if (preg_match('/tháng$/u', $lbl)) {
            return 'month';
        }
        if (preg_match('/năm$/u', $lbl)) {
            return 'year';
        }
        if (($f['type'] ?? '') === 'date') {
            return 'vndate';
        }
        return null;
    }

    /** Kiểm giá trị ngày/tháng/năm gõ tay (khớp qf-date.js). Trống = hợp lệ. */
    public static function dateValueValid(?string $kind, string $v): bool
    {
        $v = trim($v);
        if ($kind === null || $v === '') {
            return true;
        }
        if ($kind === 'day') {
            return ctype_digit($v) && (int) $v >= 1 && (int) $v <= 31;
        }
        if ($kind === 'month') {
            return ctype_digit($v) && (int) $v >= 1 && (int) $v <= 12;
        }
        if ($kind === 'year') {
            return (bool) preg_match('/^\d{4}$/', $v) && (int) $v >= 1900 && (int) $v <= 2100;
        }
        if ($kind === 'vndate') {
            return (bool) preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $v, $m) && checkdate((int) $m[2], (int) $m[1], (int) $m[3]);
        }
        return true;
    }

    /** Độ rộng ô theo hệ 6 khối/dòng (1..6). Ô to → 6 (nguyên dòng), ô thường → 3 (nửa dòng), ô nhỏ → 1-2. */
    public static function fieldSpan(array $f): int
    {
        $type = $f['type'] ?? 'text';
        if (in_array($type, ['textarea', 'repeatable_table'], true)) {
            return 6;
        }
        if (in_array($type, ['select', 'radio'], true) && count($f['options'] ?? []) > 4) {
            return 6;
        }
        $dk = self::dateKind($f);
        if (in_array($dk, ['day', 'month', 'year'], true)) {
            return 2;
        }
        if ($dk === 'vndate') {
            return 3;
        }
        if ($type === 'number') {
            return 2;
        }
        if ($type === 'checkbox') {
            return 2;
        }
        return 3;   // chữ ngắn = nửa dòng
    }

    /** Rà tất cả ô ngày/tháng/năm trong mọi ngày; trả nhãn ô sai (để chặn lưu). */
    private function invalidDateLabels(): array
    {
        $kinds = [];
        foreach ($this->fields as $f) {
            $k = self::dateKind($f);
            if ($k) {
                $kinds[$f['key']] = ['kind' => $k, 'label' => $f['label'] ?? $f['key']];
            }
        }
        $bad = [];
        foreach ($this->rows as $row) {
            foreach ($kinds as $key => $meta) {
                $v = (string) ($row['data'][$key] ?? '');
                if (! self::dateValueValid($meta['kind'], $v)) {
                    $bad[$meta['label']] = true;
                }
            }
        }
        return array_keys($bad);
    }

    /** Cột STT/số thứ tự → tự đánh số theo dòng, không nhập tay. */
    public static function isSttCol(array $col): bool
    {
        $k = mb_strtolower(trim($col['key'] ?? ''), 'UTF-8');
        $l = mb_strtolower(trim($col['label'] ?? ''), 'UTF-8');
        return in_array($k, ['stt', 'tt', 'so_tt', 'sott', 'so_thu_tu'], true)
            || preg_match('/^(stt|tt|s\.?t\.?t|số\s*(tt|thứ\s*tự)|#)$/u', $l);
    }

    public function getUsedDatesProperty(): array
    {
        return collect($this->rows)->pluck('ngay')->all();
    }

    public function setActive(int $i): void
    {
        if (isset($this->rows[$i])) {
            $this->active = $i;
        }
    }

    public function addDay(?string $ngay = null): void
    {
        $ngay = $ngay ?: now()->toDateString();
        $idx  = array_search($ngay, array_column($this->rows, 'ngay'), true);
        if ($idx !== false) {
            $this->active = (int) $idx;
            return;
        }
        $tables = [];
        foreach ($this->tableFields as $tf) {
            $tables[$tf['key']] = [];
        }
        $this->rows[]  = ['id' => null, 'ngay' => $ngay, 'data' => [], 'tables' => $tables, 'trang_thai' => 'nhap_dang_do', 'attachments' => []];
        $this->rows    = array_values($this->rows);
        $this->active  = count($this->rows) - 1;
    }

    /** Nút "+ Ngày": thêm 1 tab với ngày trống gần nhất (lùi dần từ hôm nay). */
    public function addNewDay(): void
    {
        $d     = Carbon::today();
        $used  = $this->usedDates;
        $guard = 0;
        while (in_array($d->toDateString(), $used, true) && $guard++ < 400) {
            $d->subDay();
        }
        $this->addDay($d->toDateString());
    }

    public function addAllEmptyOfMonth(): void
    {
        [$y, $m] = explode('-', $this->thang);
        $day   = Carbon::create((int) $y, (int) $m, 1);
        $end   = $day->copy()->endOfMonth();
        $today = now()->toDateString();
        for ($d = $day->copy(); $d->lte($end); $d->addDay()) {
            $ds = $d->toDateString();
            if ($ds <= $today && ! in_array($ds, $this->usedDates, true)) {
                $this->addDay($ds);
            }
        }
    }

    /** Mở panel sao chép — mặc định tháng theo ngày đang chọn. */
    public function openCopy(): void
    {
        $src = $this->rows[$this->active]['ngay'] ?? now()->toDateString();
        $this->copyMonth = Carbon::parse($src)->format('Y-m');
        $this->copyDates = [];
        $this->showCopy  = true;
    }

    /** Danh sách ngày trong tháng đang chọn (để tick). */
    public function getCopyDaysProperty(): array
    {
        if (! $this->copyMonth) {
            return [];
        }
        [$y, $m] = array_map('intval', explode('-', $this->copyMonth));
        $s   = Carbon::create($y, $m, 1);
        $e   = $s->copy()->endOfMonth();
        $out = [];
        for ($d = $s->copy(); $d->lte($e); $d->addDay()) {
            $out[] = $d->toDateString();
        }
        return $out;
    }

    public function toggleCopyDate(string $date): void
    {
        if (($k = array_search($date, $this->copyDates, true)) !== false) {
            unset($this->copyDates[$k]);
        } else {
            $this->copyDates[] = $date;
        }
        $this->copyDates = array_values($this->copyDates);
    }

    public function selectAllCopyDates(): void
    {
        $src = $this->rows[$this->active]['ngay'] ?? null;
        $this->copyDates = array_values(array_filter($this->copyDays, fn ($d) => $d !== $src));
    }

    public function clearCopyDates(): void
    {
        $this->copyDates = [];
    }

    /** Sao chép data + bảng của ngày đang chọn sang các ngày đã tick (tạo tab mới nếu chưa có, ghi đè nếu đã có). */
    public function copyToSelected(): void
    {
        if (! isset($this->rows[$this->active])) {
            return;
        }
        $src     = $this->rows[$this->active];
        $srcDate = $src['ngay'];
        $data    = $src['data'] ?? [];
        $tables  = $src['tables'] ?? [];
        $n = 0;
        foreach ($this->copyDates as $date) {
            if (! $date || $date === $srcDate) {
                continue;
            }
            $idx = array_search($date, array_column($this->rows, 'ngay'), true);
            if ($idx !== false) {
                $this->rows[$idx]['data']       = $data;
                $this->rows[$idx]['tables']     = $tables;
                $this->rows[$idx]['trang_thai'] = 'nhap_dang_do';
            } else {
                $this->rows[] = ['id' => null, 'ngay' => $date, 'data' => $data, 'tables' => $tables, 'trang_thai' => 'nhap_dang_do', 'attachments' => []];
            }
            $n++;
        }
        $this->rows      = array_values($this->rows);
        $this->showCopy  = false;
        $this->copyDates = [];
        ActivityLogger::log('copy', 'Sao chép dữ liệu ngày ' . Carbon::parse($srcDate)->format('d/m/Y') . " sang {$n} ngày — biểu mẫu " . $this->version->formTemplate->ma_bm);
        session()->flash('success', 'Đã sao chép dữ liệu ngày ' . Carbon::parse($srcDate)->format('d/m/Y') . " sang {$n} ngày. Bấm “Lưu tất cả” để lưu.");
    }

    public function removeRow(int $i): void
    {
        if (! isset($this->rows[$i])) {
            return;
        }
        if ($this->rows[$i]['id']) {
            $this->deletedIds[] = $this->rows[$i]['id'];
        }
        array_splice($this->rows, $i, 1);
        $this->rows   = array_values($this->rows);
        if (empty($this->rows)) {
            $this->addDay(now()->toDateString());
        }
        $this->active = max(0, min($this->active, count($this->rows) - 1));
    }

    public function addTableRow(string $fieldKey): void
    {
        $tf = collect($this->tableFields)->firstWhere('key', $fieldKey);
        if (! $tf) {
            return;
        }
        $empty = [];
        foreach ($tf['columns'] ?? [] as $c) {
            $empty[$c['key']] = '';
        }
        $this->rows[$this->active]['tables'][$fieldKey][] = $empty;
    }

    public function removeTableRow(string $fieldKey, int $ri): void
    {
        if (isset($this->rows[$this->active]['tables'][$fieldKey][$ri])) {
            array_splice($this->rows[$this->active]['tables'][$fieldKey], $ri, 1);
            $this->rows[$this->active]['tables'][$fieldKey] = array_values($this->rows[$this->active]['tables'][$fieldKey]);
        }
    }

    public function saveTitle(): void
    {
        $t = trim($this->title);
        if ($t === '') {
            $this->title = $this->version->formTemplate->ten_bm;
            return;
        }
        $this->version->formTemplate->update(['ten_bm' => $t]);
        session()->flash('success', 'Đã đổi tên biểu mẫu.');
    }

    /** Danh sách đính kèm của 1 submission -> mảng cho blade. */
    private function attList($subId): array
    {
        return FormSubmissionAttachment::where('form_submission_id', $subId)->latest('id')->get()
            ->map(fn ($a) => [
                'id' => $a->id, 'name' => $a->original_name, 'mime' => $a->mime,
                'size' => $a->size, 'is_image' => $a->isImage(),
            ])->all();
    }

    private function reloadAttachments(int $i): void
    {
        $id = $this->rows[$i]['id'] ?? null;
        $this->rows[$i]['attachments'] = $id ? $this->attList($id) : [];
    }

    /** Lưu riêng 1 ngày (để có submission id trước khi đính kèm). */
    private function persistRow(int $i): ?FormSubmission
    {
        if (empty($this->rows[$i]['ngay'])) {
            return null;
        }
        $row = $this->rows[$i];
        $sub = FormSubmission::updateOrCreate(
            ['form_template_version_id' => $this->versionId, 'user_id' => auth()->id(), 'ngay_nhap' => $row['ngay']],
            ['data_json' => $row['data'] ?? [], 'trang_thai' => $row['trang_thai'] ?? 'nhap_dang_do']
        );
        foreach (($row['tables'] ?? []) as $fk => $trows) {
            $tf      = collect($this->tableFields)->firstWhere('key', $fk);
            $sttKeys = collect($tf['columns'] ?? [])->filter(fn ($c) => self::isSttCol($c))->pluck('key')->all();
            FormSubmissionRow::where('form_submission_id', $sub->id)->where('field_key', $fk)->delete();
            foreach ($trows as $ri => $rd) {
                foreach ($sttKeys as $sk) {
                    $rd[$sk] = $ri + 1;
                }
                FormSubmissionRow::create(['form_submission_id' => $sub->id, 'field_key' => $fk, 'row_index' => $ri, 'row_data_json' => $rd]);
            }
        }
        $this->rows[$i]['id'] = $sub->id;
        return $sub;
    }

    /** Tự động lưu (im lặng) ngày đang chọn — KHÔNG ghi nhật ký để tránh spam. */
    public function autosave(): void
    {
        if ($this->persistRow($this->active)) {
            $this->savedAt = now()->format('H:i');
            $this->dispatch('saved');
        }
    }

    /** Livewire tự gọi khi chọn tệp -> lưu ngày hiện tại (để có id) rồi đính kèm. */
    public function updatedUploads(): void
    {
        $this->validate([
            'uploads.*' => 'file|max:20480|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt',
        ], [], ['uploads.*' => 'tệp']);

        $sub = $this->persistRow($this->active);
        if (! $sub) {
            $this->uploads = [];
            return;
        }
        foreach ($this->uploads as $file) {
            $path = $file->store("attachments/{$sub->id}", 'local');
            FormSubmissionAttachment::create([
                'form_submission_id' => $sub->id,
                'path'               => $path,
                'original_name'      => $file->getClientOriginalName(),
                'mime'               => $file->getMimeType(),
                'size'               => $file->getSize(),
            ]);
        }
        $this->uploads = [];
        $this->reloadAttachments($this->active);
        ActivityLogger::log('upload', 'Đính kèm tệp vào biểu mẫu ' . $this->version->formTemplate->ma_bm . ' — ngày ' . ($this->rows[$this->active]['ngay'] ?? ''), $sub);
        session()->flash('success', 'Đã tải lên tệp đính kèm.');
    }

    public function deleteAttachment(int $id): void
    {
        $a = FormSubmissionAttachment::find($id);
        if (! $a) {
            return;
        }
        $sub = $a->submission;
        if ($sub && $sub->user_id !== auth()->id() && ! (auth()->user()->is_admin ?? false)) {
            return;
        }
        Storage::disk('local')->delete($a->path);
        $subId = $a->form_submission_id;
        ActivityLogger::log('delete_attachment', 'Xoá tệp đính kèm: ' . $a->original_name);
        $a->delete();
        foreach ($this->rows as $i => $r) {
            if (($r['id'] ?? null) === $subId) {
                $this->reloadAttachments($i);
            }
        }
    }

    public function saveAll(): void
    {
        $dates = array_column($this->rows, 'ngay');
        if (count($dates) !== count(array_unique($dates))) {
            $this->addError('rows', 'Có ngày bị trùng — mỗi ngày chỉ một bản ghi.');
            return;
        }

        // Chặn lưu nếu ô ngày/tháng/năm gõ tay sai định dạng
        if ($badDates = $this->invalidDateLabels()) {
            $this->addError('rows', 'Ngày/tháng/năm nhập sai, sửa lại rồi lưu: ' . implode(', ', $badDates)
                . ' (Ngày 1–31, Tháng 1–12, Năm 4 chữ số, ngày đầy đủ dạng dd/mm/yyyy).');
            return;
        }

        DB::transaction(function () {
            foreach ($this->deletedIds as $id) {
                FormSubmission::where('id', $id)->where('user_id', auth()->id())->delete();
            }
            $this->deletedIds = [];

            foreach ($this->rows as $i => $row) {
                if (empty($row['ngay'])) {
                    continue;
                }
                $sub = FormSubmission::updateOrCreate(
                    ['form_template_version_id' => $this->versionId, 'user_id' => auth()->id(), 'ngay_nhap' => $row['ngay']],
                    ['data_json' => $row['data'] ?? [], 'trang_thai' => 'hoan_thanh']
                );
                foreach (($row['tables'] ?? []) as $fk => $trows) {
                    $tf      = collect($this->tableFields)->firstWhere('key', $fk);
                    $sttKeys = collect($tf['columns'] ?? [])->filter(fn ($c) => self::isSttCol($c))->pluck('key')->all();
                    FormSubmissionRow::where('form_submission_id', $sub->id)->where('field_key', $fk)->delete();
                    foreach ($trows as $ri => $rd) {
                        foreach ($sttKeys as $sk) {
                            $rd[$sk] = $ri + 1;                 // STT luôn theo số dòng
                        }
                        FormSubmissionRow::create([
                            'form_submission_id' => $sub->id, 'field_key' => $fk,
                            'row_index' => $ri, 'row_data_json' => $rd,
                        ]);
                    }
                }
                $this->rows[$i]['id']         = $sub->id;
                $this->rows[$i]['trang_thai'] = 'hoan_thanh';
            }
        });

        ActivityLogger::log('save', 'Lưu biểu mẫu ' . $this->version->formTemplate->ma_bm . ' (' . count($this->rows) . ' ngày)');
        $this->savedAt = now()->format('H:i');
        $this->dispatch('saved');

        // Cảnh báo (không chặn) các ô BẮT BUỘC còn trống ở ngày đã có dữ liệu.
        $required = collect($this->fields)->where('required', true)->pluck('label', 'key');
        $missing  = [];
        foreach ($this->rows as $row) {
            $hasData = ! empty(array_filter($row['data'] ?? [])) || ! empty(array_filter($row['tables'] ?? []));
            if (! $hasData) {
                continue;
            }
            foreach ($required as $k => $lbl) {
                if (($row['data'][$k] ?? '') === '' || ($row['data'][$k] ?? null) === null) {
                    $missing[$lbl] = true;
                }
            }
        }

        if ($missing) {
            session()->flash('warning', 'Đã lưu, nhưng còn thiếu ô bắt buộc: ' . implode(', ', array_keys($missing)));
        } else {
            session()->flash('success', 'Đã lưu ' . count($this->rows) . ' ngày.');
        }
    }

    public function render()
    {
        return view('livewire.register-fill');
    }
}
