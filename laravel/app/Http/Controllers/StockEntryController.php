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
                'image'   => $p->image_path ? route('item.image', $p->id) : '',
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

    /** Tạo nhanh một thẻ kho ngay tại màn chọn (kèm ảnh nếu có). */
    public function quickCreate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'  => 'required|string|max:80',
            'name'  => 'required|string|max:190',
            'unit'  => 'required|string|max:40',
            'group' => 'nullable|string|max:120',
            'packing' => 'nullable|string|max:190',
            'expiry' => 'nullable|date',
            'min'   => 'nullable|numeric|min:0',
            'max'   => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:8192',
        ]);
        $code = trim($data['code']);
        if (StockProduct::whereRaw('LOWER(code) = ?', [mb_strtolower($code)])->exists()) {
            return response()->json(['ok' => false, 'errors' => ["Mã hàng \"{$code}\" đã có thẻ kho"]], 422);
        }

        $p = StockProduct::create([
            'ext_id'     => 'p' . (StockProduct::max('id') + 1) . '-' . substr(md5($code . microtime()), 0, 5),
            'card_no'    => StockCardController::nextCardNo(),
            'code'       => $code,
            'name'       => trim($data['name']),
            'group_name' => $data['group'] ?? null,
            'unit'       => trim($data['unit']),
            'packing'    => $data['packing'] ?? null,
            'expiry'     => ($data['expiry'] ?? '') ?: null,
            'min_qty'    => $data['min'] ?? 0,
            'max_qty'    => $data['max'] ?? 0,
            'active'     => true,
        ]);

        if ($request->hasFile('image')) {
            $p->update(['image_path' => $this->storeImage($request->file('image'), $p->ext_id)]);
        }

        ActivityLogger::log('stock_item', "Tạo nhanh thẻ kho {$p->card_no} cho mã hàng {$code}");

        return response()->json(['ok' => true, 'id' => $p->ext_id, 'cardNo' => $p->card_no]);
    }

    /** Đổi ảnh cho một mã hàng đã có. */
    public function setImage(Request $request, StockProduct $product): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:8192']);
        if ($product->image_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($product->image_path)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($product->image_path);
        }
        $product->update(['image_path' => $this->storeImage($request->file('image'), $product->ext_id)]);

        return response()->json(['ok' => true, 'url' => route('item.image', $product->id)]);
    }

    /** Trả ảnh mã hàng. */
    public function image(StockProduct $product)
    {
        abort_unless($product->image_path, 404);
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        abort_unless($disk->exists($product->image_path), 404);

        return response($disk->get($product->image_path), 200, [
            'Content-Type'  => $disk->mimeType($product->image_path) ?: 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    /** Thu nhỏ ảnh về tối đa 640px cho nhẹ rồi lưu vào storage. */
    private function storeImage($file, string $ext): string
    {
        $dir  = 'stock_images';
        $name = $dir . '/' . $ext . '-' . substr(md5(microtime()), 0, 6) . '.jpg';
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $disk->makeDirectory($dir);

        $src = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $src) {                                   // không đọc được -> lưu nguyên bản
            $raw = $dir . '/' . $ext . '-' . substr(md5(microtime()), 0, 6) . '.' . $file->getClientOriginalExtension();
            $disk->put($raw, file_get_contents($file->getRealPath()));

            return $raw;
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $max = 640;
        if ($w > $max || $h > $max) {
            $r  = min($max / $w, $max / $h);
            $nw = (int) round($w * $r);
            $nh = (int) round($h * $r);
            $dst = imagecreatetruecolor($nw, $nh);
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }
        ob_start();
        imagejpeg($src, null, 82);
        $bin = ob_get_clean();
        imagedestroy($src);
        $disk->put($name, $bin);

        return $name;
    }
}
