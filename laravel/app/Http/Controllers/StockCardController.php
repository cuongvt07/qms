<?php

namespace App\Http\Controllers;

use App\Models\StockProduct;
use App\Models\StockTransaction;
use App\Services\ActivityLogger;
use App\Services\XlsxWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module quản lý thẻ kho.
 * Mỗi mã hàng có đúng một thẻ kho; số thẻ kho (card_no) được tự sinh khi tạo sản phẩm.
 */
class StockCardController extends Controller
{
    public function page()
    {
        return view('modules.stock-card');
    }

    /** Sinh số thẻ kho kế tiếp: TK-00001, TK-00002… */
    public static function nextCardNo(): string
    {
        $max = 0;
        foreach (StockProduct::pluck('card_no') as $c) {
            if (preg_match('/TK-(\d+)/', (string) $c, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return 'TK-' . str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }

    private function seedIfEmpty(): void
    {
        if (StockProduct::exists()) {
            return;
        }
        $products = [
            ['p1', '994646', 'Paraffin Pearls', 'Kg', '1 Kg/Túi', '', 10, 100],
            ['p2', 'V.019.001', 'Ống ly tâm nước tiểu MDL Tube PP16*100, CAP', 'Cái', '250 Cái/Túi', '', 500, 5000],
            ['p3', '801501', 'Ống ly tâm CELLPRO 50ml', 'Cái', '25 Cái/Túi', '', 100, 1000],
            ['p4', '775500', 'Toluen For HPLC & Spectroscopy', 'Chai', '4 Chai 2,5L/Thùng', 'Công ty TNHH phát triển công nghệ An Đô', 20, 100],
            ['p5', '4511', 'Tissue-Tek Paraffin Wax TEK III (Polymer)', 'Kg', '2,5 Kg/Túi', 'Công ty TNHH Qa-Lab Việt Nam', 5, 20],
        ];
        foreach ($products as $i => [$id, $code, $name, $unit, $packing, $supplier, $min, $max]) {
            StockProduct::create([
                'ext_id' => $id, 'card_no' => 'TK-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'code' => $code, 'name' => $name, 'unit' => $unit, 'packing' => $packing,
                'supplier' => $supplier, 'min_qty' => $min, 'max_qty' => $max,
            ]);
        }
        $tx = [
            ['p1', '2026-01-01', 'import', 31, '250720', '2030-02-01', '', 'Khoa Dược', 'Nguyễn Quang Thi', ''],
            ['p1', '2026-01-05', 'export', 1, '250720', '2030-02-01', 'Sinh thiết nhỏ', 'Sơn', 'Đức', ''],
            ['p1', '2026-01-10', 'export', 4, '250720', '2030-02-01', 'Sinh thiết nhỏ', 'Thi', 'Quyến', ''],
            ['p1', '2026-01-12', 'export', 4, '250720', '2030-02-01', 'Sinh thiết nhỏ', 'Thi', 'Đức', ''],
            ['p1', '2026-02-05', 'import', 20, '250720', '2030-02-01', '', 'Khoa Dược', 'Thi', 'Nhập bổ sung'],
            ['p2', '2026-04-29', 'import', 500, 'V150126', '2031-01-15', '', 'Kho vật tư hầm B1', 'Thi', ''],
            ['p2', '2026-05-29', 'import', 250, 'V160326', '2031-03-16', '', 'Kho vật tư hầm B1', 'Thi', ''],
            ['p2', '2026-07-10', 'export', 100, 'V150126', '2031-01-15', 'Phòng tế bào', 'Đức', 'Thái', ''],
            ['p5', '2026-06-02', 'import', 7.5, '518947', null, '', 'C Nga - Khoa Dược', 'Thi', ''],
            ['p5', '2026-06-03', 'export', 1, '518947', null, 'Sinh thiết nhỏ', 'Thi', 'Yến', ''],
            ['p5', '2026-06-03', 'export', 2.5, '518947', null, 'Sinh thiết nhỏ', 'Đức', 'Quyến', ''],
        ];
        foreach ($tx as $i => [$pid, $date, $type, $qty, $batch, $expiry, $dest, $deliver, $receive, $note]) {
            StockTransaction::create([
                'ext_id' => 'stx-' . ($i + 1), 'product_ext_id' => $pid, 'date' => $date, 'type' => $type,
                'qty' => $qty, 'batch' => $batch, 'expiry' => $expiry ?: null, 'destination' => $dest,
                'deliverer' => $deliver, 'receiver' => $receive, 'note' => $note,
            ]);
        }
    }

    public function state(): JsonResponse
    {
        $this->seedIfEmpty();

        return response()->json([
            'products' => StockProduct::orderBy('id')->get()->map(fn ($p) => [
                'id'       => $p->ext_id,
                'cardNo'   => $p->card_no,
                'code'     => $p->code,
                'name'     => $p->name,
                'unit'     => $p->unit ?? '',
                'packing'  => $p->packing ?? '',
                'supplier' => $p->supplier ?? '',
                'expiry'   => $p->expiry?->toDateString() ?? '',
                'min'      => (float) $p->min_qty,
                'max'      => (float) $p->max_qty,
            ])->all(),
            'transactions' => StockTransaction::orderBy('date')->orderBy('id')->get()->map(fn ($t) => [
                'id'          => $t->ext_id,
                'productId'   => $t->product_ext_id,
                'date'        => $t->date?->toDateString(),
                'type'        => $t->type,
                'qty'         => (float) $t->qty,
                'actual'      => $t->actual === null ? null : (float) $t->actual,
                'batch'       => $t->batch ?? '',
                'expiry'      => $t->expiry?->toDateString() ?? '',
                'destination' => $t->destination ?? '',
                'deliverer'   => $t->deliverer ?? '',
                'receiver'    => $t->receiver ?? '',
                'note'        => $t->note ?? '',
            ])->all(),
        ]);
    }

    /** Xuất một thẻ kho ra Excel theo đúng bố cục phiếu giấy BM.01/QTQL.26. */
    public function exportCard(Request $request)
    {
        $this->seedIfEmpty();
        $ext = trim((string) $request->query('product', ''));
        $p   = StockProduct::where('ext_id', $ext)->first();
        abort_unless($p, 404);

        $tx  = StockTransaction::where('product_ext_id', $p->ext_id)
            ->orderBy('date')->orderBy('id')->get();

        $bal  = 0.0;
        $rows = [];
        foreach ($tx as $t) {
            $nhap = $xuat = $huy = null;
            if ($t->type === 'import') {
                $bal += (float) $t->qty;
                $nhap = (float) $t->qty;
            } elseif ($t->type === 'export') {
                $bal -= (float) $t->qty;
                $xuat = (float) $t->qty;
            } elseif ($t->type === 'destroy') {
                $bal -= (float) $t->qty;
                $huy = (float) $t->qty;
            } elseif ($t->type === 'adjust') {
                $bal = (float) $t->actual;
            }
            $rows[] = [
                $t->date?->format('d/m/Y') ?? '',
                $nhap, $t->type === 'import' ? ($t->batch ?? '') : '',
                $t->type === 'import' ? ($t->expiry?->format('d/m/Y') ?? '') : '',
                $xuat, $t->destination ?? '',
                $t->type === 'export' ? ($t->batch ?? '') : '',
                $t->type === 'export' ? ($t->expiry?->format('d/m/Y') ?? '') : '',
                $huy, round($bal, 2),
                $t->type === 'adjust' ? (float) $t->actual : null,
                $t->deliverer ?? '', $t->receiver ?? '', $t->note ?? '',
            ];
        }

        return XlsxWriter::taiVe('the-kho-' . $p->code . '.xlsx', [[
            'name'  => 'Thẻ kho ' . $p->card_no,
            'title' => 'THẺ KHO',
            'note'  => [
                'Mã số tài liệu: BM.01/QTQL.26 · Phiên bản 2.25 · Số thẻ kho: ' . $p->card_no,
                'Tên hàng hóa: ' . $p->name . '   |   Mã hàng: ' . $p->code . '   |   Đơn vị tính: ' . ($p->unit ?: '—'),
                'Quy cách đóng gói: ' . ($p->packing ?: '—') . '   |   Công ty cung cấp: ' . ($p->supplier ?: '—'),
                'Hạn hóa chất / vật tư: ' . ($p->expiry?->format('d/m/Y') ?: '—')
                    . '   |   Tồn tối thiểu: ' . rtrim(rtrim(number_format((float) $p->min_qty, 2, '.', ''), '0'), '.')
                    . '   |   Tồn tối đa: ' . rtrim(rtrim(number_format((float) $p->max_qty, 2, '.', ''), '0'), '.'),
                'Tồn hiện tại: ' . rtrim(rtrim(number_format($bal, 2, '.', ''), '0'), '.') . ' ' . ($p->unit ?: ''),
            ],
            'header' => ['Ngày tháng', 'SL nhập (a)', 'Số lô nhập', 'HSD nhập', 'SL xuất (b)', 'Nơi nhận',
                'Số lô xuất', 'HSD xuất', 'Hủy / quá hạn (c)', 'Tồn (d)', 'Đếm kho thực tế',
                'Người giao', 'Người nhận', 'Ghi chú'],
            'widths' => [12, 11, 13, 12, 11, 18, 13, 12, 13, 11, 13, 16, 16, 26],
            'rows'   => $rows,
        ]]);
    }

    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'products'     => 'array',
            'transactions' => 'array',
        ]);
        $products = $request->input('products', []);
        $tx       = $request->input('transactions', []);

        $keep = [];
        foreach ($products as $p) {
            if (empty($p['id']) || trim($p['code'] ?? '') === '') {
                continue;
            }
            // Tự sinh số thẻ kho nếu sản phẩm mới chưa có
            $cardNo = $p['cardNo'] ?? '';
            if (! $cardNo) {
                $existing = StockProduct::where('ext_id', $p['id'])->first();
                $cardNo = $existing->card_no ?? self::nextCardNo();
            }
            StockProduct::updateOrCreate(['ext_id' => $p['id']], [
                'card_no'  => $cardNo,
                'code'     => $p['code'],
                'name'     => $p['name'] ?? '',
                'unit'     => $p['unit'] ?? null,
                'packing'  => $p['packing'] ?? null,
                'supplier' => $p['supplier'] ?? null,
                'expiry'   => ($p['expiry'] ?? '') ?: null,
                'min_qty'  => $p['min'] ?? 0,
                'max_qty'  => $p['max'] ?? 0,
            ]);
            $keep[] = $p['id'];
        }
        $removed = StockProduct::whereNotIn('ext_id', $keep ?: ['__none__'])->pluck('ext_id');
        StockProduct::whereIn('ext_id', $removed)->delete();
        StockTransaction::whereIn('product_ext_id', $removed)->delete();

        $keepTx = [];
        foreach ($tx as $t) {
            if (empty($t['id']) || empty($t['productId'])) {
                continue;
            }
            StockTransaction::updateOrCreate(['ext_id' => $t['id']], [
                'product_ext_id' => $t['productId'],
                'date'           => $t['date'] ?? null,
                'type'           => $t['type'] ?? 'import',
                'qty'            => $t['qty'] ?? 0,
                'actual'         => ($t['actual'] ?? '') === '' ? null : $t['actual'],
                'batch'          => $t['batch'] ?? null,
                'expiry'         => ($t['expiry'] ?? '') ?: null,
                'destination'    => $t['destination'] ?? null,
                'deliverer'      => $t['deliverer'] ?? null,
                'receiver'       => $t['receiver'] ?? null,
                'note'           => $t['note'] ?? null,
            ]);
            $keepTx[] = $t['id'];
        }
        StockTransaction::whereNotIn('ext_id', $keepTx ?: ['__none__'])->delete();

        ActivityLogger::log('stock_card', 'Cập nhật thẻ kho (' . count($products) . ' mã hàng)');

        return response()->json(['ok' => true, 'nextCardNo' => self::nextCardNo()]);
    }
}
