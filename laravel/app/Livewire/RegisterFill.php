<?php

namespace App\Livewire;

use App\Models\FormSubmission;
use App\Models\FormSubmissionAttachment;
use App\Models\FormSubmissionRow;
use App\Models\FormTemplateVersion;
use App\Services\ActivityLogger;
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
        $fields = array_values(array_map(function ($f) use ($clean) {
            $f['label'] = $clean($f['label'] ?? '');
            if (! empty($f['columns'])) {
                $f['columns'] = array_map(function ($c) use ($clean) {
                    $c['label'] = $clean($c['label'] ?? '');
                    return $c;
                }, $f['columns']);
            }
            return $f;
        }, array_filter($this->version->fields, fn ($f) => empty($f['hidden']))));
        usort($fields, fn ($a, $b) => $posOf($a) <=> $posOf($b));   // PHP 8 usort ổn định
        return $fields;
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
            $path = Storage::disk('local')->path($this->version->formTemplate->file_goc_path);
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
