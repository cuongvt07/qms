<?php

namespace App\Http\Controllers;

use App\Models\StockProduct;
use App\Models\StockTransaction;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Quản lý mã hàng + tổng quan kho.
 * Mã hàng ↔ thẻ kho là quan hệ 1–1: tạo mã hàng là có ngay thẻ kho (card_no tự sinh),
 * mọi phát sinh nhập/xuất/hủy/kiểm kê đều ghi trên thẻ kho của chính mã hàng đó.
 */
class StockItemController extends Controller
{
    public function page()
    {
        return view('modules.stock-items');
    }

    public function dashboardPage()
    {
        return view('modules.stock-dashboard');
    }

    /** Tồn hiện tại của 1 mã hàng (kiểm kê ghi đè tồn). */
    private function balance($rows): float
    {
        $b = 0.0;
        foreach ($rows as $t) {
            if ($t->type === 'import') {
                $b += (float) $t->qty;
            } elseif ($t->type === 'export' || $t->type === 'destroy') {
                $b -= (float) $t->qty;
            } elseif ($t->type === 'adjust') {
                $b = (float) $t->actual;
            }
        }

        return $b;
    }

    private function statusOf(StockProduct $p, float $b): string
    {
        if ($b <= 0) {
            return 'out';
        }
        if ($p->max_qty > 0 && $b > $p->max_qty) {
            return 'high';
        }
        if ($p->min_qty > 0 && $b <= $p->min_qty) {
            return 'low';
        }

        return 'ok';
    }

    /** ===== Danh mục mã hàng ===== */
    public function state(): JsonResponse
    {
        app(StockCardController::class)->state();   // đảm bảo dữ liệu nền đã có

        $tx = StockTransaction::orderBy('date')->orderBy('id')->get()->groupBy('product_ext_id');

        $items = StockProduct::orderBy('id')->get()->map(function ($p) use ($tx) {
            $rows = $tx->get($p->ext_id, collect());
            $b    = $this->balance($rows);
            $last = $rows->last();

            return [
                'id'       => $p->ext_id,
                'cardNo'   => $p->card_no,
                'code'     => $p->code,
                'name'     => $p->name,
                'group'    => $p->group_name ?? '',
                'unit'     => $p->unit ?? '',
                'packing'  => $p->packing ?? '',
                'supplier' => $p->supplier ?? '',
                'min'      => (float) $p->min_qty,
                'max'      => (float) $p->max_qty,
                'active'   => (bool) $p->active,
                'note'     => $p->note ?? '',
                'balance'  => $b,
                'status'   => $this->statusOf($p, $b),
                'txCount'  => $rows->count(),
                'lastDate' => $last?->date?->toDateString() ?? '',
            ];
        })->all();

        return response()->json([
            'items'  => $items,
            'groups' => StockProduct::whereNotNull('group_name')->where('group_name', '!=', '')
                ->distinct()->orderBy('group_name')->pluck('group_name')->all(),
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $request->validate(['items' => 'array']);
        $items = $request->input('items', []);

        $keep = [];
        foreach ($items as $it) {
            $code = trim($it['code'] ?? '');
            if ($code === '') {
                continue;
            }
            $ext = $it['id'] ?? '';
            $row = $ext ? StockProduct::where('ext_id', $ext)->first() : null;
            if (! $row) {
                // mã hàng mới -> tự sinh thẻ kho
                $ext = $ext ?: 'p' . (StockProduct::max('id') + 1) . '-' . substr(md5($code), 0, 5);
                $row = new StockProduct(['ext_id' => $ext, 'card_no' => StockCardController::nextCardNo()]);
            }
            $row->fill([
                'code'       => $code,
                'name'       => $it['name'] ?? '',
                'group_name' => $it['group'] ?? null,
                'unit'       => $it['unit'] ?? null,
                'packing'    => $it['packing'] ?? null,
                'supplier'   => $it['supplier'] ?? null,
                'min_qty'    => $it['min'] ?? 0,
                'max_qty'    => $it['max'] ?? 0,
                'active'     => (bool) ($it['active'] ?? true),
                'note'       => $it['note'] ?? null,
            ])->save();
            $keep[] = $row->ext_id;
        }

        // Xoá mã hàng bị bỏ khỏi danh sách -> xoá luôn thẻ kho & phát sinh của nó
        $removed = StockProduct::whereNotIn('ext_id', $keep ?: ['__none__'])->pluck('ext_id');
        if ($removed->count()) {
            StockTransaction::whereIn('product_ext_id', $removed)->delete();
            StockProduct::whereIn('ext_id', $removed)->delete();
        }

        ActivityLogger::log('stock_item', 'Cập nhật danh mục mã hàng (' . count($items) . ' mã)');

        return response()->json(['ok' => true]);
    }

    /** ===== Số liệu tổng quan kho ===== */
    public function dashboard(Request $request): JsonResponse
    {
        app(StockCardController::class)->state();

        $year = (int) ($request->query('year') ?: date('Y'));
        $tx   = StockTransaction::orderBy('date')->orderBy('id')->get();
        $byP  = $tx->groupBy('product_ext_id');

        $items = [];
        $sumLow = $sumOut = $sumHigh = 0;
        foreach (StockProduct::orderBy('id')->get() as $p) {
            $rows = $byP->get($p->ext_id, collect());
            $b    = $this->balance($rows);
            $st   = $this->statusOf($p, $b);
            $sumLow  += $st === 'low' ? 1 : 0;
            $sumOut  += $st === 'out' ? 1 : 0;
            $sumHigh += $st === 'high' ? 1 : 0;

            // tiêu thụ (xuất + hủy) theo 12 tháng của năm đang xem
            $months = array_fill(1, 12, 0.0);
            $imp    = array_fill(1, 12, 0.0);
            foreach ($rows as $t) {
                if ((int) $t->date->format('Y') !== $year) {
                    continue;
                }
                $m = (int) $t->date->format('n');
                if ($t->type === 'export' || $t->type === 'destroy') {
                    $months[$m] += (float) $t->qty;
                } elseif ($t->type === 'import') {
                    $imp[$m] += (float) $t->qty;
                }
            }

            // tồn cuối mỗi tháng
            $run = 0.0;
            $endOfMonth = array_fill(1, 12, 0.0);
            foreach ($rows as $t) {
                $y = (int) $t->date->format('Y');
                if ($y > $year) {
                    break;
                }
                if ($t->type === 'import') {
                    $run += (float) $t->qty;
                } elseif ($t->type === 'export' || $t->type === 'destroy') {
                    $run -= (float) $t->qty;
                } elseif ($t->type === 'adjust') {
                    $run = (float) $t->actual;
                }
                if ($y === $year) {
                    $endOfMonth[(int) $t->date->format('n')] = $run;
                }
            }
            $carry = 0.0;
            for ($m = 1; $m <= 12; $m++) {
                if ($endOfMonth[$m] == 0.0 && ($imp[$m] + $months[$m]) == 0.0) {
                    $endOfMonth[$m] = $carry;
                } else {
                    $carry = $endOfMonth[$m];
                }
            }

            $items[] = [
                'id'       => $p->ext_id,
                'cardNo'   => $p->card_no,
                'code'     => $p->code,
                'name'     => $p->name,
                'group'    => $p->group_name ?? '',
                'unit'     => $p->unit ?? '',
                'min'      => (float) $p->min_qty,
                'max'      => (float) $p->max_qty,
                'balance'  => $b,
                'status'   => $st,
                'used'     => array_values($months),
                'imported' => array_values($imp),
                'stockEnd' => array_values($endOfMonth),
                'usedYear' => array_sum($months),
            ];
        }

        // lô sắp hết hạn (còn tồn theo lô)
        $batches = [];
        foreach ($byP as $pid => $rows) {
            $map = [];
            foreach ($rows as $t) {
                if (! $t->batch) {
                    continue;
                }
                $k = $t->batch;
                $map[$k] ??= ['batch' => $k, 'expiry' => $t->expiry?->toDateString() ?? '', 'qty' => 0.0];
                if ($t->type === 'import') {
                    $map[$k]['qty'] += (float) $t->qty;
                } elseif ($t->type === 'export' || $t->type === 'destroy') {
                    $map[$k]['qty'] -= (float) $t->qty;
                }
            }
            $p = StockProduct::where('ext_id', $pid)->first();
            foreach ($map as $b) {
                if ($b['qty'] <= 0 || ! $b['expiry']) {
                    continue;
                }
                $days = (int) now()->startOfDay()->diffInDays($b['expiry'], false);
                if ($days > 180) {
                    continue;
                }
                $batches[] = [
                    'code' => $p?->code, 'name' => $p?->name, 'unit' => $p?->unit,
                    'batch' => $b['batch'], 'expiry' => $b['expiry'], 'qty' => $b['qty'], 'days' => $days,
                ];
            }
        }
        usort($batches, fn ($a, $b) => $a['days'] <=> $b['days']);

        return response()->json([
            'year'   => $year,
            'years'  => $tx->map(fn ($t) => (int) $t->date->format('Y'))->unique()->sort()->values()->all(),
            'items'  => $items,
            'totals' => [
                'items' => count($items), 'low' => $sumLow, 'out' => $sumOut, 'high' => $sumHigh,
                'tx'    => $tx->count(),
            ],
            'batches' => array_slice($batches, 0, 20),
        ]);
    }
}
