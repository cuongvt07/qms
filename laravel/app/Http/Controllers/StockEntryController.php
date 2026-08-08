<?php

namespace App\Http\Controllers;

use App\Models\QmsStaff;
use App\Models\StockProduct;
use App\Models\StockTransaction;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Màn nhập / xuất nhanh: chọn nhiều thẻ kho một lúc (kiểu đặt hàng), chỉ nhập số lượng.
 * Ngày và người thực hiện được ghi thẳng vào các cột tương ứng của từng thẻ kho.
 */
class StockEntryController extends Controller
{
    public function page()
    {
        return view('modules.stock-entry');
    }

    /** Tồn hiện tại theo mã hàng + tồn theo từng lô. */
    private function computed(): array
    {
        $tx = StockTransaction::orderBy('date')->orderBy('id')->get()->groupBy('product_ext_id');
        $bal = [];
        $batches = [];
        foreach ($tx as $pid => $rows) {
            $b = 0.0;
            $map = [];
            foreach ($rows as $t) {
                if ($t->type === 'import') {
                    $b += (float) $t->qty;
                } elseif ($t->type === 'export' || $t->type === 'destroy') {
                    $b -= (float) $t->qty;
                } elseif ($t->type === 'adjust') {
                    $b = (float) $t->actual;
                }
                if ($t->batch) {
                    $map[$t->batch] ??= ['batch' => $t->batch, 'expiry' => $t->expiry?->toDateString() ?? '', 'qty' => 0.0];
                    if ($t->type === 'import') {
                        $map[$t->batch]['qty'] += (float) $t->qty;
                    } elseif ($t->type === 'export' || $t->type === 'destroy') {
                        $map[$t->batch]['qty'] -= (float) $t->qty;
                    }
                    if ($t->expiry) {
                        $map[$t->batch]['expiry'] = $t->expiry->toDateString();
                    }
                }
            }
            $bal[$pid] = $b;
            $list = array_values(array_filter($map, fn ($x) => $x['qty'] > 0));
            usort($list, fn ($a, $c) => ($a['expiry'] ?: '9999') <=> ($c['expiry'] ?: '9999'));  // FEFO
            $batches[$pid] = $list;
        }

        return [$bal, $batches];
    }

    public function state(): JsonResponse
    {
        app(StockCardController::class)->state();   // đảm bảo có dữ liệu nền
        [$bal, $batches] = $this->computed();

        $items = StockProduct::where('active', true)->orderBy('code')->get()->map(function ($p) use ($bal, $batches) {
            $b = $bal[$p->ext_id] ?? 0.0;
            $st = $b <= 0 ? 'out'
                : ($p->max_qty > 0 && $b > $p->max_qty ? 'high'
                    : ($p->min_qty > 0 && $b <= $p->min_qty ? 'low' : 'ok'));

            return [
                'id'      => $p->ext_id,
                'cardNo'  => $p->card_no,
                'code'    => $p->code,
                'name'    => $p->name,
                'group'   => $p->group_name ?? '',
                'unit'    => $p->unit ?? '',
                'packing' => $p->packing ?? '',
                'min'     => (float) $p->min_qty,
                'max'     => (float) $p->max_qty,
                'balance' => $b,
                'status'  => $st,
                'batches' => $batches[$p->ext_id] ?? [],
            ];
        })->values()->all();

        return response()->json([
            'items'  => $items,
            'groups' => collect($items)->pluck('group')->filter()->unique()->sort()->values()->all(),
            'staff'  => QmsStaff::where('active', true)->orderBy('id')->pluck('name')->all(),
            'me'     => auth()->user()?->name ?? '',
            'today'  => now()->toDateString(),
            'recent' => StockTransaction::orderByDesc('id')->limit(12)->get()->map(function ($t) {
                $p = StockProduct::where('ext_id', $t->product_ext_id)->first();

                return [
                    'code' => $p?->code, 'name' => $p?->name, 'unit' => $p?->unit,
                    'type' => $t->type, 'qty' => (float) $t->qty,
                    'date' => $t->date?->toDateString(), 'by' => $t->created_by ?? '',
                ];
            })->all(),
        ]);
    }

    /** Ghi một loạt phát sinh cho nhiều thẻ kho cùng lúc. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'          => 'required|in:import,export,destroy',
            'date'          => 'required|date',
            'deliverer'     => 'nullable|string|max:190',
            'receiver'      => 'nullable|string|max:190',
            'destination'   => 'nullable|string|max:190',
            'note'          => 'nullable|string',
            'lines'         => 'required|array|min:1',
            'lines.*.id'    => 'required|string',
            'lines.*.qty'   => 'required|numeric|gt:0',
        ]);
        $lines = $request->input('lines', []);
        $type  = $data['type'];
        $me    = auth()->user()?->name ?? '';

        [$bal, $batches] = $this->computed();

        // Kiểm tra trước toàn bộ, sai một dòng thì không ghi dòng nào
        $errors = [];
        foreach ($lines as $l) {
            $p = StockProduct::where('ext_id', $l['id'])->first();
            if (! $p) {
                $errors[] = 'Không tìm thấy mã hàng ' . $l['id'];
                continue;
            }
            $qty = (float) $l['qty'];
            if ($type === 'export' || $type === 'destroy') {
                $have = $bal[$p->ext_id] ?? 0.0;
                if ($qty > $have) {
                    $errors[] = "{$p->code}: xuất {$qty} vượt tồn hiện có {$have} {$p->unit}";
                    continue;
                }
                $batch = $l['batch'] ?? '';
                if ($batch) {
                    $found = collect($batches[$p->ext_id] ?? [])->firstWhere('batch', $batch);
                    if ($found && $qty > $found['qty']) {
                        $errors[] = "{$p->code}: lô {$batch} chỉ còn {$found['qty']} {$p->unit}";
                    }
                }
            }
        }
        if ($errors) {
            return response()->json(['ok' => false, 'errors' => $errors], 422);
        }

        $saved = 0;
        DB::transaction(function () use ($lines, $type, $data, $me, &$saved) {
            foreach ($lines as $i => $l) {
                $p = StockProduct::where('ext_id', $l['id'])->first();
                if (! $p) {
                    continue;
                }
                StockTransaction::create([
                    'ext_id'         => 'stx-' . now()->timestamp . '-' . $i . '-' . substr(md5($p->ext_id . $i), 0, 4),
                    'product_ext_id' => $p->ext_id,
                    'date'           => $data['date'],
                    'type'           => $type,
                    'qty'            => (float) $l['qty'],
                    'batch'          => $l['batch'] ?? null,
                    'expiry'         => ($l['expiry'] ?? '') ?: null,
                    'destination'    => $type === 'import' ? null : ($data['destination'] ?? null),
                    'deliverer'      => $data['deliverer'] ?? null,
                    'receiver'       => $data['receiver'] ?? null,
                    'note'           => $l['note'] ?? ($data['note'] ?? null),
                    'created_by'     => $me,
                ]);
                $saved++;
            }
        });

        $label = ['import' => 'Nhập kho', 'export' => 'Xuất kho', 'destroy' => 'Hủy/quá hạn'][$type];
        ActivityLogger::log('stock_entry', "{$label} nhanh: {$saved} mã hàng ngày " . $data['date']);

        [$bal] = $this->computed();

        return response()->json(['ok' => true, 'saved' => $saved, 'balances' => $bal]);
    }
}
