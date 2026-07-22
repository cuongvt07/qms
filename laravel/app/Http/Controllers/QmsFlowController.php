<?php

namespace App\Http\Controllers;

use App\Models\DeviceEvent;
use App\Models\EnvRecord;
use App\Models\QmsFlowStep;
use App\Models\QmsOption;
use App\Models\WasteRow;
use Illuminate\Http\JsonResponse;

/**
 * Luồng nhập liệu nối tiếp: lưu xong popup module này thì mở popup module kế tiếp.
 * Bước nào đã có dữ liệu của hôm nay thì coi như xong và được bỏ qua.
 */
class QmsFlowController extends Controller
{
    /** Trang + tên hàm mở popup tương ứng của từng bước. */
    public const ACTIONS = [
        'env'    => ['daily' => 'Thêm bản ghi hôm nay', 'month' => 'Nhập nhanh theo tháng'],
        'device' => ['event' => 'Thêm sự kiện', 'batch' => 'Nhập nhiều thiết bị'],
        'waste'  => ['batch' => 'Nhập nhiều dòng', 'single' => 'Thêm một dòng'],
    ];

    private const ROUTES = ['env' => 'env.page', 'device' => 'dev.page', 'waste' => 'waste.page'];

    public function page()
    {
        return view('modules.flow-runner');
    }

    /** Bước đã có dữ liệu của hôm nay chưa. */
    private function doneToday(string $module): bool
    {
        $today = now()->toDateString();

        return match ($module) {
            'env'    => EnvRecord::whereDate('date', $today)->exists(),
            'device' => DeviceEvent::whereDate('date', $today)->exists(),
            'waste'  => WasteRow::whereDate('date', $today)->exists(),
            default  => false,
        };
    }

    /** Danh sách bước + trạng thái hôm nay + bước kế tiếp cần nhập. */
    public function state(): JsonResponse
    {
        $steps = QmsFlowStep::where('active', true)->orderBy('sort')->orderBy('id')->get()
            ->map(function ($s) {
                return [
                    'id'     => $s->id,
                    'module' => $s->module,
                    'action' => $s->action,
                    'label'  => $s->label ?: (self::ACTIONS[$s->module][$s->action] ?? $s->action),
                    'url'    => route(self::ROUTES[$s->module] ?? 'env.page'),
                    'done'   => $this->doneToday($s->module),
                ];
            })->values()->all();

        $next = collect($steps)->firstWhere('done', false);

        return response()->json([
            'enabled'  => count($steps) > 0,
            'autoOpen' => (bool) QmsOption::val('flow_auto_open', true),
            'steps'    => $steps,
            'next'     => $next,
        ]);
    }

    /** Điểm vào sau khi đăng nhập: mở trang nhập liệu theo luồng nếu có khai báo. */
    public function entry()
    {
        $has = QmsFlowStep::where('active', true)->exists();
        if (! $has || ! QmsOption::val('flow_auto_open', true)) {
            return redirect()->route('env.page');
        }

        return redirect()->route('flow.page');
    }
}
