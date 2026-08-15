<?php

namespace App\Http\Controllers;

use App\Models\IhcMarker;
use App\Models\SlideConsult;
use App\Models\SlideConsultImage;
use App\Models\SlideConsultNote;
use App\Models\SlideHistory;
use App\Models\SlideIhc;
use App\Models\SlidePatient;
use App\Models\SlideRecord;
use App\Services\ActivityLogger;
use App\Services\XlsxWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Sổ soạn tiêu bản + hóa mô miễn dịch.
 *
 * Mã tiêu bản = 2 số cuối năm + chữ cái + 4 chữ số thứ tự (26C2472), duy nhất toàn hệ thống.
 * Sổ chia theo đầu mã (26A, 26B…); mỗi đầu mã trải sẵn 9999 dòng ở giao diện, nhưng chỉ
 * dòng nào có người nhập số block / số tiêu bản mới được ghi xuống CSDL.
 */
class SlideBookController extends Controller
{
    private const SO_DONG = 9999;

    public function page()
    {
        return view('modules.slide-book');
    }

    private function me(): string
    {
        return auth()->user()?->name ?? '';
    }

    /** Nạp danh mục marker lần đầu từ file seed. */
    private function seedMarkers(): void
    {
        if (IhcMarker::exists()) {
            return;
        }
        $file = database_path('seed/ihc_markers.json');
        if (! is_file($file)) {
            return;
        }
        foreach (json_decode(file_get_contents($file), true) ?: [] as $m) {
            $ten = trim($m['ten'] ?? '');
            if ($ten === '') {
                continue;
            }
            IhcMarker::firstOrCreate(['ten' => $ten], [
                'clone' => $m['clone'] ?: null,
                'hang'  => $m['hang'] ?: null,
            ]);
        }
    }

    /** Tách "26C" thành [26, 'C']; trả về đầu mã của năm hiện tại nếu không hợp lệ. */
    private function tachDauMa(?string $prefix): array
    {
        if (preg_match('/^(\d{2})([A-Z])$/', strtoupper(trim((string) $prefix)), $m)) {
            return [(int) $m[1], $m[2]];
        }

        return [(int) date('y'), 'A'];
    }

    private static function taoMa(int $yy, string $letter, int $seq): string
    {
        return sprintf('%02d%s%04d', $yy, $letter, $seq);
    }

    private function dongSo(SlideRecord $r): array
    {
        return [
            'code'      => $r->code,
            'seq'       => $r->seq,
            'soBlock'   => $r->so_block,
            'soTieuBan' => $r->so_tieu_ban,
            'ngaySoan'  => $r->ngay_soan?->toDateString() ?? '',
            'giaSo'     => $r->gia_so ?? '',
            'ktvCat'    => $r->ktv_cat ?? '',
            'ktvSoan'   => $r->ktv_soan ?? '',
            'bsDoc'     => $r->bs_doc ?? '',
            'ketQua'    => $r->ket_qua ?? '',
            'daDoc'     => (bool) $r->da_doc,
            'ttDoc'     => $r->trang_thai_doc ?: 'chua',
            'ngayNhan'  => $r->ngay_nhan?->toDateString() ?? '',
            'ngayDoc'   => $r->ngay_doc?->toDateString() ?? '',
            'ghiChu'    => $r->ghi_chu ?? '',
        ];
    }

    /** Danh sách đầu mã đã dùng + đầu mã của năm hiện tại. */
    private function dauMaList(): array
    {
        $list = SlideRecord::selectRaw('yy, letter, COUNT(*) as n, MAX(seq) as maxSeq')
            ->groupBy('yy', 'letter')->orderBy('yy')->orderBy('letter')->get()
            ->map(fn ($r) => [
                'prefix' => sprintf('%02d%s', $r->yy, $r->letter),
                'n'      => (int) $r->n,
                'maxSeq' => (int) $r->maxSeq,
            ])->keyBy('prefix')->all();

        $yy = (int) date('y');
        foreach (range('A', 'Z') as $l) {
            $p = sprintf('%02d%s', $yy, $l);
            $list[$p] ??= ['prefix' => $p, 'n' => 0, 'maxSeq' => 0];
        }
        ksort($list);

        return array_values($list);
    }

    /** ===== Sổ soạn: một đầu mã, một dải số thứ tự ===== */
    public function state(Request $request): JsonResponse
    {
        $this->seedMarkers();
        [$yy, $letter] = $this->tachDauMa($request->query('prefix'));

        // Giao diện cuộn ảo trải đủ 9999 dòng, nên trả về toàn bộ mã đã nhập của đầu mã này.
        // Chỉ dòng có dữ liệu mới nằm trong CSDL nên khối lượng luôn nhỏ.
        $rows = SlideRecord::where('yy', $yy)->where('letter', $letter)->orderBy('seq')->get()
            ->mapWithKeys(fn ($r) => [$r->seq => $this->dongSo($r)])->all();
        $from = 1;
        $to   = self::SO_DONG;

        // các mã trong dải đã có phiếu hóa mô miễn dịch / hội chẩn
        $codes   = array_map(fn ($s) => self::taoMa($yy, $letter, $s), array_keys($rows));
        $ihc     = SlideIhc::whereIn('slide_code', $codes)->pluck('slide_code')->unique()->values()->all();
        $consult = SlideConsult::whereIn('slide_code', $codes)->pluck('slide_code')->all();

        return response()->json([
            'prefix'  => sprintf('%02d%s', $yy, $letter),
            'from'    => $from,
            'to'      => $to,
            'soDong'  => self::SO_DONG,
            'rows'    => $rows,
            'coIhc'   => $ihc,
            'coHoiChan' => $consult,
            'dauMa'   => $this->dauMaList(),
            'me'      => $this->me(),
            'today'   => now()->toDateString(),
            'ktv'     => \App\Models\QmsStaff::where('active', true)->orderBy('id')->pluck('name')->all(),
        ]);
    }

    /** Lưu các dòng đã sửa trên lưới. Dòng trắng trơn thì xóa khỏi CSDL. */
    public function saveRows(Request $request): JsonResponse
    {
        $request->validate(['prefix' => 'required|string', 'rows' => 'array']);
        [$yy, $letter] = $this->tachDauMa($request->input('prefix'));
        $me    = $this->me();
        $today = now()->toDateString();

        $luu = $xoa = 0;
        foreach ($request->input('rows', []) as $r) {
            $seq = (int) ($r['seq'] ?? 0);
            if ($seq < 1 || $seq > self::SO_DONG) {
                continue;
            }
            $code = self::taoMa($yy, $letter, $seq);
            $so   = fn ($v) => ($v === '' || $v === null) ? null : (int) $v;
            $txt  = fn ($v) => trim((string) ($v ?? '')) ?: null;

            $data = [
                'so_block'    => $so($r['soBlock'] ?? null),
                'so_tieu_ban' => $so($r['soTieuBan'] ?? null),
                'gia_so'      => $txt($r['giaSo'] ?? null),
                'ktv_cat'     => $txt($r['ktvCat'] ?? null),
                'ktv_soan'    => $txt($r['ktvSoan'] ?? null),
                'bs_doc'      => $txt($r['bsDoc'] ?? null),
                'ket_qua'     => $txt($r['ketQua'] ?? null),
                'ghi_chu'     => $txt($r['ghiChu'] ?? null),
                'ngay_soan'   => ($r['ngaySoan'] ?? '') ?: null,
            ];

            $rong = collect($data)->every(fn ($v) => $v === null);
            $cu   = SlideRecord::where('code', $code)->first();

            if ($rong) {
                // chỉ xóa khi mã đó không còn dữ liệu nghiệp vụ nào bám theo
                if ($cu && ! $cu->da_doc
                    && ! SlideIhc::where('slide_code', $code)->exists()
                    && ! SlideConsult::where('slide_code', $code)->exists()) {
                    $cu->delete();
                    $xoa++;
                }
                continue;
            }

            // đã nhập số block / số tiêu bản thì ngày soạn và người soạn tự điền
            if ($data['so_block'] !== null || $data['so_tieu_ban'] !== null) {
                $data['ngay_soan'] = $data['ngay_soan'] ?: ($cu?->ngay_soan?->toDateString() ?: $today);
                $data['ktv_soan'] = $data['ktv_soan'] ?: ($cu?->ktv_soan ?: $me);
            }

            SlideRecord::updateOrCreate(
                ['code' => $code],
                $data + ['yy' => $yy, 'letter' => $letter, 'seq' => $seq]
            );
            $luu++;
        }

        if ($luu || $xoa) {
            ActivityLogger::log('slide_book', "Sổ soạn {$request->input('prefix')}: lưu {$luu} mã, xóa {$xoa} mã");
        }

        return response()->json(['ok' => true, 'saved' => $luu, 'removed' => $xoa]);
    }

    /**
     * Mã còn trống của một đầu mã để khởi tạo phiên soạn.
     *
     * Trả về TẤT CẢ mã trống nằm xen giữa các mã đã soạn (mã bị bỏ dở, mã mới ghi
     * lý do chưa ra tiêu bản), rồi nối thêm một dải mã mới sau mã lớn nhất — vì
     * từ đó trở đi mã nào cũng trống, không thể liệt kê hết 9999 dòng.
     */
    public function sessionCandidates(Request $request): JsonResponse
    {
        [$yy, $letter] = $this->tachDauMa($request->query('prefix'));
        $n  = min(max((int) $request->query('n', 50), 1), 500);
        $tu = (int) $request->query('tu', 0);

        $daSoan = array_flip(
            SlideRecord::where('yy', $yy)->where('letter', $letter)
                ->where(fn ($q) => $q->whereNotNull('so_block')->orWhereNotNull('so_tieu_ban'))
                ->pluck('seq')->all()
        );
        $maxSeq = $daSoan ? max(array_keys($daSoan)) : 0;

        $ma = [];
        // 1) mọi mã trống xen giữa — hiện hết, không cắt bớt
        for ($s = max(1, $tu); $s <= $maxSeq && count($ma) < 500; $s++) {
            if (! isset($daSoan[$s])) {
                $ma[] = ['seq' => $s, 'code' => self::taoMa($yy, $letter, $s)];
            }
        }
        $xenGiua = count($ma);

        // 2) dải mã mới tiếp theo
        for ($s = max($maxSeq + 1, $tu); $s <= self::SO_DONG && count($ma) < $xenGiua + $n; $s++) {
            if (! isset($daSoan[$s])) {
                $ma[] = ['seq' => $s, 'code' => self::taoMa($yy, $letter, $s)];
            }
        }

        // mã trống đầu tiên của sổ — phiên mới mặc định bắt đầu từ đây
        $dau = 1;
        while ($dau <= self::SO_DONG && isset($daSoan[$dau])) {
            $dau++;
        }

        return response()->json([
            'prefix'  => sprintf('%02d%s', $yy, $letter),
            'ma'      => $ma,
            'xenGiua' => $xenGiua,
            'maxSeq'  => $maxSeq,
            'dauTien' => $dau,
            'daSoan'  => count($daSoan),
            'gia'     => SlideRecord::whereNotNull('gia_so')->where('gia_so', '!=', '')
                ->orderByDesc('id')->pluck('gia_so')->unique()->take(18)->values()->all(),
            'ktv'     => \App\Models\QmsStaff::where('active', true)->orderBy('id')->pluck('name')->all(),
            'me'      => $this->me(),
            'today'   => now()->toDateString(),
        ]);
    }

    /**
     * Mã bị phiên soạn nhảy qua: nằm giữa dải mã đã soạn nhưng chưa ra tiêu bản.
     *
     * Đây là những ca phải xử lý thêm (khử canxi, cắt lại, chờ khoa gửi bù) — sổ đã
     * đi tiếp nên rất dễ quên. Tính thẳng từ khoảng trống trong sổ, không cần ai đánh dấu.
     */
    public function pendingState(): JsonResponse
    {
        $hom  = now()->startOfDay();
        $rows = SlideRecord::orderBy('yy')->orderBy('letter')->orderBy('seq')->get();
        $out  = [];

        foreach ($rows->groupBy(fn ($r) => sprintf('%02d%s', $r->yy, $r->letter)) as $prefix => $g) {
            $soan = $g->filter(fn ($r) => $r->so_block !== null || $r->so_tieu_ban !== null)->values();
            if ($soan->count() < 1) {
                continue;
            }
            // chỉ xét trong dải đã dùng của đầu mã, mã trước mã đầu tiên là chưa cấp chứ không phải bỏ quên
            $min    = $soan->min('seq');
            $max    = $soan->max('seq');
            $daSoan = $soan->keyBy('seq');
            $coDong = $g->keyBy('seq');

            for ($s = $min; $s <= $max && count($out) < 200; $s++) {
                if (isset($daSoan[$s])) {
                    continue;
                }
                $r = $coDong[$s] ?? null;
                // phiên nào đã nhảy qua mã này — lấy ngày soạn của mã kế tiếp
                $sau = $soan->first(fn ($x) => $x->seq > $s);
                $tu  = $sau?->ngay_soan;

                $out[] = [
                    'code'      => $prefix . sprintf('%04d', $s),
                    'prefix'    => $prefix,
                    'seq'       => $s,
                    'lyDo'      => $r?->ghi_chu ?? '',
                    'ngayHen'   => $r?->ngay_hen?->toDateString() ?? '',
                    'boQuaTu'   => $tu?->toDateString() ?? '',
                    'soNgayCho' => $tu ? (int) $tu->diffInDays($hom) : null,
                    'quaHen'    => (bool) ($r?->ngay_hen && $r->ngay_hen->lt($hom)),
                    'treHen'    => $r?->ngay_hen && $r->ngay_hen->lt($hom) ? (int) $r->ngay_hen->diffInDays($hom) : null,
                ];
            }
        }

        // quá hẹn lên trước, rồi tới ca chờ lâu nhất
        usort($out, fn ($a, $b) => [$b['quaHen'], $b['soNgayCho'] ?? -1] <=> [$a['quaHen'], $a['soNgayCho'] ?? -1]);

        return response()->json([
            'rows'   => $out,
            'tong'   => count($out),
            'quaHen' => count(array_filter($out, fn ($x) => $x['quaHen'])),
        ]);
    }

    /** Ghi lý do chờ và ngày hẹn ra tiêu bản cho một mã đang bị bỏ trống. */
    public function pendingSave(Request $request): JsonResponse
    {
        $d = $request->validate([
            'code'    => 'required|string|max:12',
            'ghiChu'  => 'nullable|string',
            'ngayHen' => 'nullable|date',
        ]);
        $code = strtoupper(trim($d['code']));
        if (! preg_match('/^(\d{2})([A-Z])(\d{4})$/', $code, $m)) {
            return response()->json(['ok' => false, 'errors' => ['Mã tiêu bản không hợp lệ']], 422);
        }

        $r = SlideRecord::firstOrNew(['code' => $code]);
        $r->fill([
            'yy' => (int) $m[1], 'letter' => $m[2], 'seq' => (int) $m[3],
            'ghi_chu'  => trim((string) ($d['ghiChu'] ?? '')) ?: null,
            'ngay_hen' => $d['ngayHen'] ?: null,
        ]);

        // xóa hết lý do lẫn ngày hẹn của mã chưa soạn thì gỡ luôn dòng trống khỏi sổ
        if (! $r->ghi_chu && ! $r->ngay_hen && $r->so_block === null && $r->so_tieu_ban === null) {
            if ($r->exists) {
                $r->delete();
            }

            return response()->json(['ok' => true, 'xoa' => true]);
        }
        $r->save();

        return response()->json(['ok' => true]);
    }

    /**
     * Lưu một phiên soạn: một loạt mã trống được nhập số block / số tiêu bản,
     * rồi gán chung giá và kỹ thuật viên. Ngày soạn luôn là ngày bấm lưu.
     */
    public function saveSession(Request $request): JsonResponse
    {
        $data = $request->validate([
            'prefix'           => 'required|string',
            'rows'             => 'required|array|min:1',
            'rows.*.seq'       => 'required|integer|min:1|max:' . self::SO_DONG,
            'rows.*.soBlock'   => 'nullable|integer|min:0|max:9999',
            'rows.*.soTieuBan' => 'nullable|integer|min:0|max:9999',
            'giaSo'            => 'required|string|max:20',
            'ktvCat'           => 'nullable|string|max:120',
            'ktvSoan'          => 'required|string|max:120',
            'ghiChu'           => 'nullable|string',
        ]);
        [$yy, $letter] = $this->tachDauMa($data['prefix']);

        $dong = [];
        foreach ($data['rows'] as $r) {
            $b = $r['soBlock'] ?? null;
            $t = $r['soTieuBan'] ?? null;
            if ($b === null && $t === null) {
                continue;                       // dòng để trống trong popup thì không ghi xuống sổ
            }
            $dong[(int) $r['seq']] = ['so_block' => $b, 'so_tieu_ban' => $t];
        }
        if (! $dong) {
            return response()->json(['ok' => false,
                'errors' => ['Chưa nhập số block / số tiêu bản cho mã nào']], 422);
        }

        // hai người cùng mở phiên có thể trùng mã — chặn lại thay vì đè lên phiên của người kia
        $codes = array_map(fn ($s) => self::taoMa($yy, $letter, $s), array_keys($dong));
        $trung = SlideRecord::whereIn('code', $codes)
            ->where(fn ($q) => $q->whereNotNull('so_block')->orWhereNotNull('so_tieu_ban'))
            ->pluck('code')->all();
        if ($trung) {
            return response()->json(['ok' => false, 'errors' => [
                'Các mã sau vừa có người soạn, mở lại phiên để lấy mã khác: ' . implode(', ', array_slice($trung, 0, 12)),
            ]], 422);
        }

        $chung = [
            'ngay_soan' => now()->toDateString(),
            'gia_so'    => trim($data['giaSo']),
            'ktv_cat'   => trim((string) ($data['ktvCat'] ?? '')) ?: null,
            'ktv_soan'  => trim($data['ktvSoan']),
        ];
        if (($ghiChu = trim((string) ($data['ghiChu'] ?? ''))) !== '') {
            $chung['ghi_chu'] = $ghiChu;
        }

        foreach ($dong as $seq => $v) {
            SlideRecord::updateOrCreate(
                ['code' => self::taoMa($yy, $letter, $seq)],
                $v + $chung + ['yy' => $yy, 'letter' => $letter, 'seq' => $seq]
            );
        }

        $ds     = array_keys($dong);
        sort($ds);
        $prefix = sprintf('%02d%s', $yy, $letter);
        $tu     = self::taoMa($yy, $letter, $ds[0]);
        $den    = self::taoMa($yy, $letter, end($ds));
        ActivityLogger::log('slide_book', sprintf('Phiên soạn %s: %d mã (%s – %s), giá %s, KTV soạn %s',
            $prefix, count($ds), $tu, $den, $chung['gia_so'], $chung['ktv_soan']));

        return response()->json(['ok' => true, 'n' => count($ds), 'tu' => $tu, 'den' => $den, 'seqDau' => $ds[0]]);
    }

    /** ===== Màn bác sĩ đọc: gom theo giá ===== */
    public function readerState(Request $request): JsonResponse
    {
        $gia = trim((string) $request->query('gia', ''));

        $giaList = SlideRecord::whereNotNull('gia_so')->where('gia_so', '!=', '')
            ->selectRaw("gia_so, COUNT(*) n,
                SUM(trang_thai_doc = 'chua') chua,
                SUM(trang_thai_doc = 'nhan') nhan,
                SUM(trang_thai_doc = 'doc') doc,
                MAX(ngay_soan) ngaySoan,
                GROUP_CONCAT(DISTINCT NULLIF(bs_doc, '') ORDER BY bs_doc SEPARATOR ', ') bs")
            ->groupBy('gia_so')->orderByRaw('CAST(gia_so AS UNSIGNED), gia_so')->get()
            ->map(fn ($r) => [
                'gia'      => $r->gia_so,
                'n'        => (int) $r->n,
                'chua'     => (int) $r->chua,
                'nhan'     => (int) $r->nhan,
                'doc'      => (int) $r->doc,
                'bs'       => (string) ($r->bs ?? ''),
                'ngaySoan' => $r->ngaySoan ? substr((string) $r->ngaySoan, 0, 10) : '',
            ])->all();

        $rows = [];
        if ($gia !== '') {
            $rs   = SlideRecord::where('gia_so', $gia)->orderBy('yy')->orderBy('letter')->orderBy('seq')->get();
            $ihc  = SlideIhc::whereIn('slide_code', $rs->pluck('code'))->get()->keyBy('slide_code');
            $hc   = SlideConsult::whereIn('slide_code', $rs->pluck('code'))->get()->keyBy('slide_code');
            $rows = $rs->map(function ($r) use ($ihc, $hc) {
                $h = $ihc->get($r->code);
                $c = $hc->get($r->code);

                return $this->dongSo($r) + [
                    'markers'  => $h?->markers ?? [],
                    'coIhc'    => (bool) $h,
                    'hoiChan'  => $c ? ($c->ket_luan ? 'chot' : 'mo') : '',
                ];
            })->all();
        }

        return response()->json([
            'giaList' => $giaList,
            'gia'     => $gia,
            'rows'    => $rows,
            'me'      => $this->me(),
            'bs'      => \App\Models\QmsStaff::where('active', true)->orderBy('id')->pluck('name')->all(),
        ]);
    }

    /**
     * Bác sĩ nhận một hay nhiều giá về đọc: cả giá được gán tên bác sĩ
     * và chuyển sang "đã nhận". Mã nào đã đọc xong thì giữ nguyên người đã đọc.
     */
    public function takeRacks(Request $request): JsonResponse
    {
        $d = $request->validate([
            'gia'   => 'required|array|min:1',
            'gia.*' => 'string|max:20',
            'bs'    => 'required|string|max:120',
        ]);
        $bs = trim($d['bs']);

        $nhan = $boQua = 0;
        foreach (SlideRecord::whereIn('gia_so', $d['gia'])->get() as $r) {
            if ($r->trang_thai_doc === 'doc') {
                $boQua++;
                continue;
            }
            $r->datTrangThaiDoc('nhan', $bs);
            $r->save();
            $nhan++;
        }
        ActivityLogger::log('slide_book', sprintf('BS %s nhận giá %s — %d mã tiêu bản',
            $bs, implode(', ', $d['gia']), $nhan));

        return response()->json(['ok' => true, 'n' => $nhan, 'boQua' => $boQua]);
    }

    /** Tích chọn nhiều mã rồi chuyển trạng thái đọc: chưa đọc / đã nhận / đã đọc. */
    public function markRead(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codes'     => 'required|array|min:1',
            'codes.*'   => 'string',
            'trangThai' => 'required|in:chua,nhan,doc',
            'bsDoc'     => 'nullable|string|max:120',
        ]);
        $bs = trim((string) ($data['bsDoc'] ?? '')) ?: null;

        $n = 0;
        foreach (SlideRecord::whereIn('code', $data['codes'])->get() as $r) {
            $r->datTrangThaiDoc($data['trangThai'], $bs);
            $r->save();
            $n++;
        }
        $ten = ['chua' => 'chưa đọc', 'nhan' => 'đã nhận', 'doc' => 'đã đọc kết quả'][$data['trangThai']];
        ActivityLogger::log('slide_book', "Chuyển {$n} mã tiêu bản sang trạng thái {$ten}");

        return response()->json(['ok' => true, 'n' => $n]);
    }

    /**
     * Chốt các mã đã đọc xong: chụp cả ca vào lịch sử, dọn phiếu hóa mô / hội chẩn
     * đi kèm rồi xóa dòng trong sổ soạn để mã quay về danh sách mã trống, dùng lại cho ca sau.
     */
    public function finishSlides(Request $request): JsonResponse
    {
        $d = $request->validate(['codes' => 'required|array|min:1', 'codes.*' => 'string|max:12']);
        $me = $this->me();

        $xong = $vuong = [];
        foreach ($d['codes'] as $c) {
            $code = strtoupper(trim($c));
            $r    = SlideRecord::where('code', $code)->first();

            if (! $r) {
                $vuong[] = "{$code}: không còn trong sổ soạn";
                continue;
            }
            if ($r->trang_thai_doc !== 'doc') {
                $vuong[] = "{$code}: chưa ở trạng thái đã đọc";
                continue;
            }
            if (trim((string) $r->ket_qua) === '') {
                $vuong[] = "{$code}: bác sĩ chưa nhập kết quả";
                continue;
            }

            // còn việc dở dang thì không cho chốt, kẻo xóa mất phiếu đang chạy
            $ihc = SlideIhc::where('slide_code', $code)->get();
            if ($ihc->contains(fn ($h) => $h->trang_thai !== 'doc')) {
                $vuong[] = "{$code}: phiếu hóa mô miễn dịch chưa đọc kết quả";
                continue;
            }
            $hc = SlideConsult::where('slide_code', $code)->first();
            if ($hc && ! $hc->ket_luan) {
                $vuong[] = "{$code}: phiên hội chẩn chưa chốt kết luận";
                continue;
            }

            $h0 = $ihc->last();
            $bn = SlidePatient::find($r->patient_id ?? $h0?->patient_id);

            // phiếu hóa mô và phiên hội chẩn sắp bị xóa nên phải chép nguyên văn vào lịch sử
            $sHmmd = $ihc->map(fn ($h) => [
                'markers'     => $h->markers ?? [],
                'benhNhan'    => $h->benh_nhan, 'namSinh' => $h->nam_sinh, 'doiTuong' => $h->doi_tuong,
                'khoa'        => $h->khoa, 'viTri' => $h->vi_tri, 'cdLamSang' => $h->cd_lam_sang,
                'soBlock'     => $h->so_block, 'bsChiDinh' => $h->bs_chi_dinh,
                'ngayChiDinh' => $h->ngay_chi_dinh?->toDateString(),
                'ngayLayMau'  => $h->ngay_lay_mau?->toDateString(),
                'ngayNhanMau' => $h->ngay_nhan_mau?->toDateString(),
                'ngayNhuom'   => $h->ngay_nhuom?->toDateString(),
                'bsDocKq'     => $h->bs_doc_kq, 'ngayDocKq' => $h->ngay_doc_kq?->toDateString(),
            ])->values()->all();

            $sHoiChan = $hc ? [
                'ketLuan'  => $hc->ket_luan,
                'bsChot'   => $hc->bs_chot,
                'ngayChot' => $hc->ngay_chot?->toDateString(),
                'yKien'    => $hc->notes()->orderBy('id')->get()->map(fn ($n) => [
                    'bs' => $n->bs, 'noiDung' => $n->noi_dung, 'luc' => $n->created_at?->format('d/m/Y H:i'),
                ])->all(),
            ] : null;

            SlideHistory::create([
                'code'        => $code,
                'lan'         => (int) SlideHistory::where('code', $code)->max('lan') + 1,
                'yy'          => $r->yy, 'letter' => $r->letter, 'seq' => $r->seq,
                'so_block'    => $r->so_block, 'so_tieu_ban' => $r->so_tieu_ban,
                'ngay_soan'   => $r->ngay_soan, 'gia_so' => $r->gia_so,
                'ktv_cat'     => $r->ktv_cat, 'ktv_soan' => $r->ktv_soan,
                'bs_doc'      => $r->bs_doc, 'ket_qua' => $r->ket_qua,
                'ngay_nhan'   => $r->ngay_nhan, 'ngay_doc' => $r->ngay_doc, 'ghi_chu' => $r->ghi_chu,
                'patient_id'  => $bn?->id,
                'ma_bn'       => $bn?->ma_bn,
                'benh_nhan'   => $h0?->benh_nhan ?: $bn?->ho_ten,
                'khoa'        => $h0?->khoa ?: $bn?->khoa,
                'vi_tri'      => $h0?->vi_tri,
                'markers'     => $ihc->pluck('markers')->flatten()->filter()->unique()->values()->all() ?: null,
                'hmmd'        => $sHmmd ?: null,
                'ngay_doc_hmmd' => $h0?->ngay_doc_kq,
                'ket_luan_hoi_chan' => $hc?->ket_luan,
                'hoi_chan'    => $sHoiChan,
                'nguoi_chot'  => $me,
                'ngay_chot'   => now()->toDateString(),
            ]);

            // ảnh hội chẩn (nếu còn) xóa hẳn khỏi ổ đĩa, phiếu đi kèm đã nằm trong lịch sử
            if ($hc) {
                foreach ($hc->images()->get() as $a) {
                    Storage::disk('local')->delete($a->path);
                }
                $hc->delete();
            }
            SlideIhc::where('slide_code', $code)->delete();
            $r->delete();
            $xong[] = $code;
        }

        if ($xong) {
            ActivityLogger::log('slide_book', sprintf('Hoàn tất %d mã tiêu bản vào lịch sử: %s',
                count($xong), implode(', ', array_slice($xong, 0, 20))));
        }

        return response()->json(['ok' => true, 'n' => count($xong), 'xong' => $xong, 'vuong' => $vuong]);
    }

    private function goiLichSu(SlideHistory $h): array
    {
        return [
            'id'        => $h->id,
            'code'      => $h->code,
            'lan'       => (int) $h->lan,
            'soBlock'   => $h->so_block,
            'soTieuBan' => $h->so_tieu_ban,
            'ngaySoan'  => $h->ngay_soan?->toDateString() ?? '',
            'giaSo'     => $h->gia_so ?? '',
            'ktvCat'    => $h->ktv_cat ?? '',
            'ktvSoan'   => $h->ktv_soan ?? '',
            'bsDoc'     => $h->bs_doc ?? '',
            'ketQua'    => $h->ket_qua ?? '',
            'ngayDoc'   => $h->ngay_doc?->toDateString() ?? '',
            'ngayChot'  => $h->ngay_chot?->toDateString() ?? '',
            'nguoiChot' => $h->nguoi_chot ?? '',
            'maBn'      => $h->ma_bn ?? '',
            'benhNhan'  => $h->benh_nhan ?? '',
            'khoa'      => $h->khoa ?? '',
            'viTri'     => $h->vi_tri ?? '',
            'markers'   => $h->markers ?? [],
            'ketLuanHoiChan' => $h->ket_luan_hoi_chan ?? '',
            'ghiChu'    => $h->ghi_chu ?? '',
            'ngayNhan'  => $h->ngay_nhan?->toDateString() ?? '',
            'ngayDocHmmd' => $h->ngay_doc_hmmd?->toDateString() ?? '',
            'hmmd'      => $h->hmmd ?? [],
            'hoiChan'   => $h->hoi_chan,
        ];
    }

    /** Dòng lịch sử khớp từ khóa tìm — dùng chung cho màn xem và bản xuất Excel. */
    private function timLichSu(string $q)
    {
        return SlideHistory::orderByDesc('ngay_chot')->orderByDesc('id')
            ->when($q !== '', fn ($b) => $b->where(fn ($w) => $w
                ->where('code', 'like', "%{$q}%")
                ->orWhere('benh_nhan', 'like', "%{$q}%")
                ->orWhere('ma_bn', 'like', "%{$q}%")
                ->orWhere('gia_so', $q)
                ->orWhere('bs_doc', 'like', "%{$q}%")
                ->orWhere('ktv_soan', 'like', "%{$q}%")
                ->orWhere('ghi_chu', 'like', "%{$q}%")
                ->orWhere('ket_qua', 'like', "%{$q}%")));
    }

    /** Lịch sử các lượt đã hoàn tất — nằm trong tab Sổ hóa mô miễn dịch. */
    public function historyState(Request $request): JsonResponse
    {
        $rows = $this->timLichSu(trim((string) $request->query('q', '')))
            ->limit(400)->get()->map(fn ($h) => $this->goiLichSu($h))->all();

        return response()->json(['rows' => $rows, 'tong' => SlideHistory::count()]);
    }

    public function exportHistory(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $rows = $this->timLichSu($q)->get()->map(fn ($h) => [
            $h->code, (int) $h->lan, $h->so_block, $h->so_tieu_ban,
            $h->ngay_soan?->format('d/m/Y') ?? '', $h->gia_so ?? '',
            $h->ktv_cat ?? '', $h->ktv_soan ?? '', $h->bs_doc ?? '',
            $h->ngay_nhan?->format('d/m/Y') ?? '', $h->ngay_doc?->format('d/m/Y') ?? '',
            $h->ket_qua ?? '', $h->ma_bn ?? '', $h->benh_nhan ?? '', $h->khoa ?? '', $h->vi_tri ?? '',
            implode(', ', $h->markers ?? []), $h->ket_luan_hoi_chan ?? '', $h->ghi_chu ?? '',
            $h->ngay_chot?->format('d/m/Y') ?? '', $h->nguoi_chot ?? '',
        ])->all();

        return XlsxWriter::taiVe('lich-su-tieu-ban-da-hoan-tat.xlsx', [[
            'name'   => 'Lịch sử hoàn tất',
            'title'  => 'LỊCH SỬ MÃ TIÊU BẢN ĐÃ HOÀN TẤT',
            'note'   => ['Xuất ngày ' . now()->format('d/m/Y H:i') . ' · ' . count($rows) . ' lượt'
                . ($q !== '' ? ' · lọc theo "' . $q . '"' : '')],
            'header' => ['Mã tiêu bản', 'Lượt', 'Số block', 'Số tiêu bản', 'Ngày soạn', 'Giá', 'KTV cắt',
                'KTV soạn', 'BS đọc', 'Ngày nhận', 'Ngày đọc', 'Kết quả / đánh giá', 'Mã BN', 'Bệnh nhân',
                'Khoa', 'Vị trí lấy mẫu', 'Marker HMMD', 'Kết luận hội chẩn', 'Ghi chú', 'Ngày hoàn tất', 'Người chốt'],
            'widths' => [14, 6, 9, 10, 12, 7, 14, 14, 16, 12, 12, 40, 12, 22, 12, 20, 30, 34, 22, 13, 16],
            'rows'   => $rows,
        ]]);
    }

    /** ===== Hóa mô miễn dịch ===== */
    public function ihcState(Request $request): JsonResponse
    {
        $this->seedMarkers();
        $tt = trim((string) $request->query('tt', ''));

        $q = SlideIhc::orderByDesc('id');
        if ($tt !== '') {
            $q->where('trang_thai', $tt);
        }

        return response()->json([
            'rows' => $q->get()->map(fn ($h) => [
                'id'         => $h->id,
                'code'       => $h->slide_code,
                'maBn'       => SlidePatient::find($h->patient_id)?->ma_bn ?? '',
                'benhNhan'   => $h->benh_nhan ?? '',
                'namSinh'    => $h->nam_sinh ?? '',
                'doiTuong'   => $h->doi_tuong ?? '',
                'khoa'       => $h->khoa ?? '',
                'viTri'      => $h->vi_tri ?? '',
                'cdLamSang'  => $h->cd_lam_sang ?? '',
                'soBlock'    => $h->so_block,
                'markers'    => $h->markers ?? [],
                'bsChiDinh'  => $h->bs_chi_dinh ?? '',
                'ngayLayMau' => $h->ngay_lay_mau?->toDateString() ?? '',
                'ngayNhanMau' => $h->ngay_nhan_mau?->toDateString() ?? '',
                'ngayNhuom'  => $h->ngay_nhuom?->toDateString() ?? '',
                'bsDocKq'    => $h->bs_doc_kq ?? '',
                'ngayDocKq'  => $h->ngay_doc_kq?->toDateString() ?? '',
                'trangThai'  => $h->trang_thai,
            ])->all(),
            'markers' => IhcMarker::where('active', true)->orderBy('ten')
                ->get(['ten', 'clone', 'hang'])->all(),
            'me' => $this->me(),
        ]);
    }

    /** Tạo / cập nhật phiếu hóa mô miễn dịch cho một mã tiêu bản. */
    public function ihcSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'      => 'required|string|max:12',
            'markers'   => 'array',
            'maBn'      => 'nullable|string|max:40',
            'benhNhan'  => 'nullable|string|max:190',
            'namSinh'   => 'nullable|string|max:10',
            'doiTuong'  => 'nullable|string|max:40',
            'khoa'      => 'nullable|string|max:60',
            'viTri'     => 'nullable|string|max:190',
            'cdLamSang' => 'nullable|string|max:255',
            'soBlock'   => 'nullable|integer|min:0',
            'ngayLayMau'  => 'nullable|date',
            'ngayNhanMau' => 'nullable|date',
        ]);
        $code    = strtoupper(trim($data['code']));
        $markers = array_values(array_filter(array_map('trim', $request->input('markers', []))));

        if (! SlideRecord::where('code', $code)->exists()) {
            return response()->json(['ok' => false, 'errors' => ["Mã tiêu bản {$code} chưa có trong sổ soạn"]], 422);
        }

        $h = SlideIhc::where('slide_code', $code)->first();

        if (! $markers) {                                   // bỏ hết marker = hủy chỉ định
            $h?->delete();

            return response()->json(['ok' => true, 'removed' => true]);
        }

        // Mã bệnh nhân là khóa nối "một bệnh nhân — nhiều mã tiêu bản"
        $bn = null;
        if (trim((string) ($data['maBn'] ?? '')) !== '') {
            $bn = SlidePatient::firstOrNew(['ma_bn' => trim($data['maBn'])]);
            $bn->fill(array_filter([
                'ho_ten'    => $data['benhNhan'] ?? null,
                'nam_sinh'  => $data['namSinh'] ?? null,
                'doi_tuong' => $data['doiTuong'] ?? null,
                'khoa'      => $data['khoa'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''));
            $bn->ho_ten = $bn->ho_ten ?: ($data['benhNhan'] ?? '(chưa rõ tên)');
            $bn->save();
            SlideRecord::where('code', $code)->update(['patient_id' => $bn->id]);
        }

        $h ??= new SlideIhc([
            'slide_code'    => $code,
            'bs_chi_dinh'   => $this->me(),
            'ngay_chi_dinh' => now()->toDateString(),
            'trang_thai'    => 'cho',
        ]);
        $h->fill([
            'patient_id'    => $bn?->id ?? $h->patient_id,
            'markers'       => $markers,
            'benh_nhan'     => $data['benhNhan'] ?? null,
            'nam_sinh'      => $data['namSinh'] ?? null,
            'doi_tuong'     => $data['doiTuong'] ?? null,
            'khoa'          => $data['khoa'] ?? null,
            'vi_tri'        => $data['viTri'] ?? null,
            'cd_lam_sang'   => $data['cdLamSang'] ?? null,
            'so_block'      => $data['soBlock'] ?? null,
            'ngay_lay_mau'  => $data['ngayLayMau'] ?? null,
            'ngay_nhan_mau' => $data['ngayNhanMau'] ?? null,
        ])->save();

        ActivityLogger::log('slide_ihc', "Chỉ định HMMD {$code}: " . implode(', ', $markers));

        return response()->json(['ok' => true, 'id' => $h->id]);
    }

    /** Chuyển bước phiếu HMMD: chờ nhuộm → đã nhuộm → đã đọc kết quả. */
    public function ihcStep(Request $request, SlideIhc $ihc): JsonResponse
    {
        $b = $request->validate(['buoc' => 'required|in:nhuom,doc,quay_lai'])['buoc'];

        if ($b === 'nhuom') {
            $ihc->fill(['trang_thai' => 'nhuom', 'ngay_nhuom' => now()->toDateString()]);
        } elseif ($b === 'doc') {
            $ihc->fill([
                'trang_thai'  => 'doc',
                'ngay_doc_kq' => now()->toDateString(),
                'bs_doc_kq'   => $ihc->bs_doc_kq ?: $this->me(),
            ]);
        } else {
            $ihc->fill(['trang_thai' => 'cho', 'ngay_nhuom' => null, 'ngay_doc_kq' => null]);
        }
        $ihc->save();

        return response()->json(['ok' => true, 'trangThai' => $ihc->trang_thai]);
    }

    /** ===== Hội chẩn ===== */
    private function goiHoiChan(SlideConsult $c): array
    {
        return [
            'id'       => $c->id,
            'code'     => $c->slide_code,
            'ketLuan'  => $c->ket_luan ?? '',
            'bsChot'   => $c->bs_chot ?? '',
            'ngayChot' => $c->ngay_chot?->toDateString() ?? '',
            'yKien'    => $c->notes()->orderBy('id')->get()->map(fn ($n) => [
                'bs' => $n->bs, 'noiDung' => $n->noi_dung, 'luc' => $n->created_at?->format('d/m/Y H:i'),
            ])->all(),
            'anh' => $c->images()->orderBy('id')->get()->map(fn ($a) => [
                'id' => $a->id, 'url' => route('slide.consult.image', $a->id),
                'ten' => $a->ten_goc, 'nguoi' => $a->nguoi_tai,
            ])->all(),
        ];
    }

    public function consultState(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string) $request->query('code', '')));

        $ds = SlideConsult::orderByDesc('id')->get()->map(fn ($c) => [
            'code'    => $c->slide_code,
            'chot'    => (bool) $c->ket_luan,
            'soYKien' => $c->notes()->count(),
            'soAnh'   => $c->images()->count(),
        ])->all();

        $one = null;
        if ($code !== '') {
            $c = SlideConsult::where('slide_code', $code)->first();
            $one = $c ? $this->goiHoiChan($c) : null;
        }

        return response()->json(['danhSach' => $ds, 'phien' => $one, 'me' => $this->me()]);
    }

    /** Mở phiên hội chẩn cho một mã (nếu chưa có). */
    public function consultOpen(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string) $request->validate(['code' => 'required|string|max:12'])['code']));
        if (! SlideRecord::where('code', $code)->exists()) {
            return response()->json(['ok' => false, 'errors' => ["Mã {$code} chưa có trong sổ soạn"]], 422);
        }
        $c = SlideConsult::firstOrCreate(['slide_code' => $code]);
        ActivityLogger::log('slide_consult', "Mở hội chẩn {$code}");

        return response()->json(['ok' => true, 'phien' => $this->goiHoiChan($c)]);
    }

    public function consultNote(Request $request): JsonResponse
    {
        $d = $request->validate([
            'code'    => 'required|string|max:12',
            'bs'      => 'required|string|max:120',
            'noiDung' => 'required|string',
        ]);
        $c = SlideConsult::where('slide_code', strtoupper(trim($d['code'])))->first();
        if (! $c) {
            return response()->json(['ok' => false, 'errors' => ['Chưa mở phiên hội chẩn cho mã này']], 422);
        }
        if ($c->ket_luan) {
            return response()->json(['ok' => false, 'errors' => ['Phiên đã chốt kết luận, không ghi thêm được']], 422);
        }
        SlideConsultNote::create(['consult_id' => $c->id, 'bs' => trim($d['bs']), 'noi_dung' => trim($d['noiDung'])]);

        return response()->json(['ok' => true, 'phien' => $this->goiHoiChan($c)]);
    }

    /** Tải ảnh hội chẩn (ảnh sẽ tự xóa khi chốt kết luận). */
    public function consultUpload(Request $request): JsonResponse
    {
        $d = $request->validate([
            'code'    => 'required|string|max:12',
            'anh'     => 'required|array|min:1',
            'anh.*'   => 'image|max:12288',
        ]);
        $c = SlideConsult::where('slide_code', strtoupper(trim($d['code'])))->first();
        if (! $c) {
            return response()->json(['ok' => false, 'errors' => ['Chưa mở phiên hội chẩn cho mã này']], 422);
        }
        if ($c->ket_luan) {
            return response()->json(['ok' => false, 'errors' => ['Phiên đã chốt, ảnh đã được dọn']], 422);
        }
        foreach ($request->file('anh', []) as $f) {
            SlideConsultImage::create([
                'consult_id' => $c->id,
                'path'       => $this->luuAnh($f, $c->id),
                'ten_goc'    => mb_substr($f->getClientOriginalName(), 0, 180),
                'nguoi_tai'  => $this->me(),
            ]);
        }

        return response()->json(['ok' => true, 'phien' => $this->goiHoiChan($c)]);
    }

    public function consultImage(SlideConsultImage $image)
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($image->path), 404);

        return response($disk->get($image->path), 200, [
            'Content-Type'  => $disk->mimeType($image->path) ?: 'image/jpeg',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function consultImageDelete(SlideConsultImage $image): JsonResponse
    {
        $c = SlideConsult::find($image->consult_id);
        Storage::disk('local')->delete($image->path);
        $image->delete();

        return response()->json(['ok' => true, 'phien' => $c ? $this->goiHoiChan($c) : null]);
    }

    /** Chốt kết luận hội chẩn — xóa sạch ảnh đính kèm cho nhẹ ổ đĩa. */
    public function consultClose(Request $request): JsonResponse
    {
        $d = $request->validate([
            'code'    => 'required|string|max:12',
            'bs'      => 'required|string|max:120',
            'ketLuan' => 'required|string',
        ]);
        $c = SlideConsult::where('slide_code', strtoupper(trim($d['code'])))->first();
        if (! $c) {
            return response()->json(['ok' => false, 'errors' => ['Không tìm thấy phiên hội chẩn']], 422);
        }

        $anh = $c->images()->get();
        foreach ($anh as $a) {
            Storage::disk('local')->delete($a->path);
        }
        $soAnh = $anh->count();
        $c->images()->delete();

        $c->fill([
            'ket_luan'  => trim($d['ketLuan']),
            'bs_chot'   => trim($d['bs']),
            'ngay_chot' => now()->toDateString(),
        ])->save();

        ActivityLogger::log('slide_consult', "Chốt hội chẩn {$c->slide_code}, đã dọn {$soAnh} ảnh");

        return response()->json(['ok' => true, 'soAnhDaXoa' => $soAnh, 'phien' => $this->goiHoiChan($c)]);
    }

    /** ===== Tra cứu tiến trình một mã: đã làm những xét nghiệm gì ===== */
    public function trace(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string) $request->query('code', '')));
        $r    = $code !== '' ? SlideRecord::where('code', $code)->first() : null;

        // mã đã hoàn tất thì dòng trong sổ soạn không còn, chỉ còn các lượt trong lịch sử
        $lichSu = $code === '' ? [] : SlideHistory::where('code', $code)->orderBy('lan')
            ->get()->map(fn ($h) => $this->goiLichSu($h))->all();

        if (! $r) {
            // gợi ý các mã gần đúng để bấm thẳng
            $goiY = $code === '' ? [] : SlideRecord::where('code', 'like', $code . '%')
                ->orderBy('code')->limit(12)->pluck('code')->all();

            return response()->json(['found' => false, 'goiY' => $goiY, 'lichSu' => $lichSu, 'ma' => $code]);
        }

        $ihc = SlideIhc::where('slide_code', $code)->orderBy('id')->get();
        $c   = SlideConsult::where('slide_code', $code)->first();

        $moc = [];
        if ($r->ngay_soan) {
            $moc[] = [
                'loai' => 'soan', 'ngay' => $r->ngay_soan->toDateString(), 'tieuDe' => 'Soạn tiêu bản',
                'chiTiet' => trim(sprintf('%s block · %s lam · giá %s', $r->so_block ?? '—', $r->so_tieu_ban ?? '—', $r->gia_so ?: '—')),
                'nguoi' => trim(($r->ktv_cat ? 'Cắt: ' . $r->ktv_cat . ' · ' : '') . ($r->ktv_soan ? 'Soạn: ' . $r->ktv_soan : '')),
            ];
        }
        if ($r->ngay_nhan) {
            $moc[] = [
                'loai' => 'doc', 'ngay' => $r->ngay_nhan->toDateString(), 'tieuDe' => 'Bác sĩ nhận giá về đọc',
                'chiTiet' => 'Giá ' . ($r->gia_so ?: '—'), 'nguoi' => $r->bs_doc ?? '',
            ];
        }
        if ($r->da_doc || $r->ket_qua) {
            $moc[] = [
                'loai' => 'doc', 'ngay' => $r->ngay_doc?->toDateString() ?? '', 'tieuDe' => 'Bác sĩ đọc tiêu bản',
                'chiTiet' => $r->ket_qua ?: ($r->da_doc ? 'Đã đọc' : ''), 'nguoi' => $r->bs_doc ?? '',
            ];
        }
        foreach ($ihc as $h) {
            $moc[] = [
                'loai' => 'ihc', 'ngay' => $h->ngay_chi_dinh?->toDateString() ?? '',
                'tieuDe' => 'Chỉ định hóa mô miễn dịch (' . count($h->markers ?? []) . ' marker)',
                'chiTiet' => implode(', ', $h->markers ?? []), 'nguoi' => $h->bs_chi_dinh ?? '',
            ];
            if ($h->ngay_nhuom) {
                $moc[] = ['loai' => 'nhuom', 'ngay' => $h->ngay_nhuom->toDateString(),
                    'tieuDe' => 'Nhuộm hóa mô miễn dịch', 'chiTiet' => implode(', ', $h->markers ?? []), 'nguoi' => ''];
            }
            if ($h->ngay_doc_kq) {
                $moc[] = ['loai' => 'kq', 'ngay' => $h->ngay_doc_kq->toDateString(),
                    'tieuDe' => 'Đọc kết quả hóa mô miễn dịch', 'chiTiet' => '', 'nguoi' => $h->bs_doc_kq ?? ''];
            }
        }
        if ($c) {
            foreach ($c->notes()->orderBy('id')->get() as $n) {
                $moc[] = ['loai' => 'hc', 'ngay' => $n->created_at?->toDateString() ?? '',
                    'tieuDe' => 'Ý kiến hội chẩn', 'chiTiet' => $n->noi_dung, 'nguoi' => $n->bs];
            }
            if ($c->ket_luan) {
                $moc[] = ['loai' => 'chot', 'ngay' => $c->ngay_chot?->toDateString() ?? '',
                    'tieuDe' => 'Chốt kết luận hội chẩn', 'chiTiet' => $c->ket_luan, 'nguoi' => $c->bs_chot ?? ''];
            }
        }
        usort($moc, fn ($a, $b) => ($a['ngay'] ?: '9999') <=> ($b['ngay'] ?: '9999'));

        // Bệnh nhân của mã này, và các mã khác cũng của người đó
        $bn     = SlidePatient::find($r->patient_id);
        $maKhac = $bn
            ? SlideRecord::where('patient_id', $bn->id)->where('code', '!=', $code)
                ->orderBy('code')->pluck('code')->all()
            : [];

        return response()->json([
            'found'   => true,
            'record'  => $this->dongSo($r),
            'benhNhan' => $bn ? [
                'maBn' => $bn->ma_bn, 'hoTen' => $bn->ho_ten, 'namSinh' => $bn->nam_sinh ?? '',
                'doiTuong' => $bn->doi_tuong ?? '', 'khoa' => $bn->khoa ?? '',
            ] : null,
            'maKhac'  => $maKhac,
            'ihc'     => $ihc->map(fn ($h) => [
                'id' => $h->id, 'markers' => $h->markers ?? [], 'trangThai' => $h->trang_thai,
                'benhNhan' => $h->benh_nhan ?? '', 'khoa' => $h->khoa ?? '', 'viTri' => $h->vi_tri ?? '',
            ])->all(),
            'hoiChan' => $c ? $this->goiHoiChan($c) : null,
            'lichSu'  => $lichSu,
            'moc'     => $moc,
        ]);
    }

    /** ===== Tra cứu tình trạng: bảng lọc toàn sổ ===== */
    public function statusState(Request $request): JsonResponse
    {
        $q  = trim((string) $request->query('q', ''));
        $tt = trim((string) $request->query('tt', ''));

        $rows = SlideRecord::orderByDesc('yy')->orderBy('letter')->orderBy('seq')->limit(3000)->get();
        $ihc  = SlideIhc::whereIn('slide_code', $rows->pluck('code'))->get()->groupBy('slide_code');
        $hc   = SlideConsult::whereIn('slide_code', $rows->pluck('code'))->get()->keyBy('slide_code');

        $out = [];
        $dem = ['chuaSoan' => 0, 'soan' => 0, 'nhan' => 0, 'doc' => 0, 'ihc' => 0, 'hc' => 0, 'xong' => 0];
        foreach ($rows as $r) {
            $h  = $ihc->get($r->code);
            $c  = $hc->get($r->code);
            $st = 'soan';
            if ($r->so_block === null && $r->so_tieu_ban === null) {
                $st = 'chuaSoan';                       // mới ghi lý do, chưa ra tiêu bản
            } elseif ($c && ! $c->ket_luan) {
                $st = 'hc';
            } elseif ($h && $h->contains(fn ($x) => $x->trang_thai !== 'doc')) {
                $st = 'ihc';
            } elseif ($h) {
                $st = 'xong';
            } elseif ($r->trang_thai_doc === 'doc') {
                $st = 'doc';
            } elseif ($r->trang_thai_doc === 'nhan') {
                $st = 'nhan';
            }
            $dem[$st]++;

            $hay = mb_strtolower($r->code . ' ' . $r->gia_so . ' ' . $r->bs_doc . ' ' . $r->ktv_soan . ' '
                . $r->ktv_cat . ' ' . $r->ket_qua . ' ' . $r->ghi_chu . ' '
                . ($h ? $h->pluck('benh_nhan')->implode(' ') . ' ' . $h->pluck('markers')->flatten()->implode(' ') : ''));
            if ($q !== '' && ! str_contains($hay, mb_strtolower($q))) {
                continue;
            }
            if ($tt !== '' && $tt !== $st) {
                continue;
            }

            $out[] = $this->dongSo($r) + [
                'trangThai' => $st,
                'markers'   => $h ? $h->pluck('markers')->flatten()->unique()->values()->all() : [],
                'benhNhan'  => $h?->first()?->benh_nhan ?? '',
                'khoa'      => $h?->first()?->khoa ?? '',
            ];
        }

        return response()->json(['rows' => array_slice($out, 0, 800), 'tong' => count($out), 'dem' => $dem]);
    }

    /** Tra nhanh bệnh nhân theo mã để tự điền khi chỉ định hóa mô. */
    public function patientLookup(Request $request): JsonResponse
    {
        $ma = trim((string) $request->query('ma', ''));
        $bn = $ma === '' ? null : SlidePatient::where('ma_bn', $ma)->first();

        return response()->json(['found' => (bool) $bn, 'bn' => $bn ? [
            'maBn' => $bn->ma_bn, 'hoTen' => $bn->ho_ten, 'namSinh' => $bn->nam_sinh ?? '',
            'doiTuong' => $bn->doi_tuong ?? '', 'khoa' => $bn->khoa ?? '',
            'soMa' => SlideRecord::where('patient_id', $bn->id)->count(),
        ] : null]);
    }

    /** ===== Xuất Excel ===== */
    public function exportBook(Request $request)
    {
        [$yy, $letter] = $this->tachDauMa($request->query('prefix'));
        $prefix = sprintf('%02d%s', $yy, $letter);

        $rs  = SlideRecord::where('yy', $yy)->where('letter', $letter)->orderBy('seq')->get();
        $ihc = SlideIhc::whereIn('slide_code', $rs->pluck('code'))->get()->groupBy('slide_code');

        $rows = $rs->map(function ($r) use ($ihc) {
            $h = $ihc->get($r->code);

            return [
                $r->code, $r->so_block, $r->so_tieu_ban,
                $r->ngay_soan?->format('d/m/Y') ?? '', $r->gia_so ?? '',
                $r->ktv_cat ?? '', $r->ktv_soan ?? '', $r->bs_doc ?? '',
                $r->ket_qua ?? '',
                [
                    'nhan' => 'Đã nhận ' . ($r->ngay_nhan?->format('d/m/Y') ?? ''),
                    'doc'  => 'Đã đọc ' . ($r->ngay_doc?->format('d/m/Y') ?? ''),
                ][$r->trang_thai_doc] ?? 'Chưa đọc',
                $h ? $h->pluck('markers')->flatten()->unique()->implode(', ') : '',
                $r->ghi_chu ?? '',
            ];
        })->all();

        return XlsxWriter::taiVe("so-soan-tieu-ban-{$prefix}.xlsx", [[
            'name'   => 'Sổ soạn ' . $prefix,
            'title'  => 'SỔ SOẠN TIÊU BẢN — ĐẦU MÃ ' . $prefix,
            'note'   => ['Xuất ngày ' . now()->format('d/m/Y H:i') . ' · ' . $rs->count() . ' mã đã nhập'],
            'header' => ['Mã tiêu bản', 'Số block', 'Số tiêu bản', 'Ngày soạn', 'Giá', 'KTV cắt', 'KTV soạn',
                'BS đọc', 'Kết quả / tình trạng', 'Trạng thái đọc', 'Marker HMMD', 'Ghi chú'],
            'widths' => [14, 9, 10, 12, 7, 14, 14, 14, 38, 16, 30, 24],
            'rows'   => $rows,
        ]]);
    }

    public function exportIhc()
    {
        $rows = SlideIhc::orderBy('id')->get()->map(fn ($h) => [
            $h->slide_code,
            SlidePatient::find($h->patient_id)?->ma_bn ?? '',
            $h->benh_nhan ?? '', $h->nam_sinh ?? '', $h->doi_tuong ?? '', $h->khoa ?? '',
            $h->vi_tri ?? '', $h->cd_lam_sang ?? '', $h->so_block,
            implode(', ', $h->markers ?? []), $h->bs_doc_kq ?? '',
            $h->ngay_lay_mau?->format('d/m/Y') ?? '', $h->ngay_nhan_mau?->format('d/m/Y') ?? '',
            $h->ngay_doc_kq?->format('d/m/Y') ?? '',
            ['cho' => 'Chờ nhuộm', 'nhuom' => 'Đã nhuộm', 'doc' => 'Đã đọc KQ'][$h->trang_thai] ?? $h->trang_thai,
        ])->all();

        return XlsxWriter::taiVe('so-hoa-mo-mien-dich.xlsx', [[
            'name'   => 'Sổ hóa mô miễn dịch',
            'title'  => 'SỔ HÓA MÔ MIỄN DỊCH',
            'note'   => ['Xuất ngày ' . now()->format('d/m/Y H:i') . ' · ' . count($rows) . ' phiếu'],
            'header' => ['Mã tiêu bản', 'Mã bệnh nhân', 'Họ và tên', 'Năm sinh', 'Đối tượng', 'Khoa',
                'Vị trí lấy mẫu', 'Chẩn đoán lâm sàng', 'SL block', 'Marker chỉ định', 'BS đọc KQ',
                'Ngày lấy mẫu', 'Ngày nhận mẫu', 'Ngày đọc KQ', 'Trạng thái'],
            'widths' => [14, 14, 24, 10, 12, 10, 20, 26, 9, 40, 14, 13, 13, 13, 14],
            'rows'   => $rows,
        ]]);
    }

    /** Thu nhỏ ảnh hội chẩn cho nhẹ rồi lưu vào storage. */
    private function luuAnh($file, int $consultId): string
    {
        $dir  = 'consult_images';
        $disk = Storage::disk('local');
        $disk->makeDirectory($dir);
        $name = $dir . '/hc' . $consultId . '-' . substr(md5(microtime() . $file->getClientOriginalName()), 0, 8) . '.jpg';

        $src = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $src) {
            $raw = $dir . '/hc' . $consultId . '-' . substr(md5(microtime()), 0, 8) . '.' . $file->getClientOriginalExtension();
            $disk->put($raw, file_get_contents($file->getRealPath()));

            return $raw;
        }
        $w   = imagesx($src);
        $h   = imagesy($src);
        $max = 1600;                                   // ảnh vi thể cần đủ nét để hội chẩn
        if ($w > $max || $h > $max) {
            $r   = min($max / $w, $max / $h);
            $nw  = (int) round($w * $r);
            $nh  = (int) round($h * $r);
            $dst = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }
        ob_start();
        imagejpeg($src, null, 85);
        $bin = ob_get_clean();
        imagedestroy($src);
        $disk->put($name, $bin);

        return $name;
    }
}
