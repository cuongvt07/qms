<?php

namespace App\Http\Controllers;

use App\Models\QmsPreset;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Lưu / lấy mẫu mặc định cho các form nhập nhiều (dùng chung mọi module). */
class QmsPresetController extends Controller
{
    private const MODULES = ['env', 'device', 'waste', 'usage'];

    public function index(string $module): JsonResponse
    {
        abort_unless(in_array($module, self::MODULES, true), 404);

        return response()->json([
            'presets' => QmsPreset::where('module', $module)->get()
                ->mapWithKeys(fn ($p) => [$p->preset_key => $p->payload])->all(),
        ]);
    }

    public function store(Request $request, string $module): JsonResponse
    {
        abort_unless(in_array($module, self::MODULES, true), 404);
        $data = $request->validate([
            'key'     => 'required|string|max:40',
            'payload' => 'required|array',
        ]);

        QmsPreset::updateOrCreate(
            ['module' => $module, 'preset_key' => $data['key']],
            ['payload' => $data['payload'], 'updated_by' => auth()->user()?->name]
        );
        ActivityLogger::log('qms_preset', "Lưu mẫu mặc định [{$module}/{$data['key']}]");

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, string $module): JsonResponse
    {
        abort_unless(in_array($module, self::MODULES, true), 404);
        QmsPreset::where('module', $module)->where('preset_key', $request->query('key', ''))->delete();

        return response()->json(['ok' => true]);
    }
}
