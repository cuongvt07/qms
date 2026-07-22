<?php

namespace App\Http\Controllers;

use App\Models\EnvSetting;
use App\Models\QmsDepartment;
use App\Models\QmsDevice;
use App\Models\QmsFlowStep;
use App\Models\QmsOption;
use App\Models\QmsStaff;
use App\Models\WasteCatalog;
use App\Models\WasteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Cấu hình chung: nhân sự, phòng ban, trang thiết bị, ngưỡng nhiệt độ/độ ẩm, danh mục rác thải.
 * Các module chỉ việc chọn từ đây, không tự tạo dữ liệu nền nữa.
 */
class QmsConfigController extends Controller
{
    private const KINDS = ['wasteTypes', 'treatments', 'locations'];

    public function page()
    {
        return view('modules.settings');
    }

    public function state(): JsonResponse
    {
        // đảm bảo dữ liệu nền của từng module đã được khởi tạo
        app(EnvMonitorController::class)->state();
        app(DeviceEventController::class)->state();
        app(WasteLogController::class)->state();

        $env   = EnvSetting::first();
        $waste = WasteSetting::first();

        $catalogs = [];
        foreach (self::KINDS as $kind) {
            $catalogs[$kind] = WasteCatalog::where('kind', $kind)->orderBy('sort')->orderBy('id')
                ->pluck('value')->all();
        }

        return response()->json([
            'staff' => QmsStaff::orderBy('id')->get()->map(fn ($u) => [
                'id'         => $u->ext_id,
                'name'       => $u->name,
                'role'       => $u->role,
                'department' => $u->department ?? '',
                'active'     => (bool) $u->active,
            ])->all(),
            'departments' => QmsDepartment::orderBy('sort')->orderBy('id')->get()
                ->map(fn ($d) => ['name' => $d->name, 'active' => (bool) $d->active])->all(),
            'devices' => QmsDevice::orderBy('id')->get()->map(fn ($d) => [
                'id'         => $d->ext_id,
                'code'       => $d->code ?? '',
                'name'       => $d->name,
                'serial'     => $d->serial ?? '',
                'location'   => $d->location ?? '',
                'department' => $d->department ?? '',
                'active'     => (bool) $d->active,
            ])->all(),
            'env' => [
                'deviceName'     => $env->device_name,
                'location'       => $env->location,
                'serial'         => $env->serial,
                'reviewer'       => $env->reviewer,
                'temperatureMin' => (float) $env->temperature_min,
                'temperatureMax' => (float) $env->temperature_max,
                'humidityMin'    => (float) $env->humidity_min,
                'humidityMax'    => (float) $env->humidity_max,
                'time1'          => $env->time1,
                'time2'          => $env->time2,
            ],
            'waste' => [
                'documentCode'  => $waste->document_code,
                'formVersion'   => $waste->form_version,
                'effectiveDate' => $waste->effective_date,
                'department'    => $waste->department,
                'year'          => (int) $waste->year,
                'catalogs'      => $catalogs,
            ],
            'flow' => [
                'autoOpen' => (bool) QmsOption::val('flow_auto_open', true),
                'steps'    => QmsFlowStep::orderBy('sort')->orderBy('id')->get()->map(fn ($s) => [
                    'module' => $s->module,
                    'action' => $s->action,
                    'label'  => $s->label ?? '',
                    'active' => (bool) $s->active,
                ])->all(),
                'actions' => QmsFlowController::ACTIONS,
            ],
            'roles' => [
                'admin'      => 'Quản trị',
                'manager'    => 'Phụ trách',
                'technician' => 'Kỹ thuật viên',
                'staff'      => 'Nhân viên',
            ],
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'staff'       => 'array',
            'departments' => 'array',
            'devices'     => 'array',
            'env'         => 'array',
            'waste'       => 'array',
            'flow'        => 'array',
        ]);
        $staff = $request->input('staff', []);
        $deps  = $request->input('departments', []);
        $devs  = $request->input('devices', []);
        $env   = $request->input('env', []);
        $waste = $request->input('waste', []);
        $flow  = $request->input('flow', null);

        // ===== Nhân sự (dùng chung 3 module) =====
        $keep = [];
        foreach ($staff as $u) {
            $name = trim($u['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $ext = $u['id'] ?? '';
            if (! $ext || str_starts_with($ext, 'new-')) {
                $ext = $this->nextExtId(QmsStaff::class, 'u-n');
            }
            QmsStaff::updateOrCreate(['ext_id' => $ext], [
                'module'     => 'all',
                'name'       => $name,
                'role'       => $u['role'] ?? 'technician',
                'department' => $u['department'] ?? null,
                'active'     => (bool) ($u['active'] ?? true),
            ]);
            $keep[] = $ext;
        }
        if ($keep) {
            // không xoá cứng để giữ lịch sử, chỉ ngừng hoạt động người bị bỏ khỏi danh sách
            QmsStaff::whereNotIn('ext_id', $keep)->update(['active' => false]);
        }

        // ===== Phòng ban =====
        if ($deps !== []) {
            QmsDepartment::query()->delete();
            foreach ($deps as $i => $d) {
                $name = trim(is_array($d) ? ($d['name'] ?? '') : $d);
                if ($name === '') {
                    continue;
                }
                QmsDepartment::updateOrCreate(['name' => $name], [
                    'active' => is_array($d) ? (bool) ($d['active'] ?? true) : true,
                    'sort'   => $i,
                ]);
            }
        }

        // ===== Trang thiết bị =====
        $keep = [];
        foreach ($devs as $d) {
            $name = trim($d['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $ext = $d['id'] ?? '';
            if (! $ext || str_starts_with($ext, 'new-')) {
                $ext = $this->nextExtId(QmsDevice::class, 'dv-n');
            }
            QmsDevice::updateOrCreate(['ext_id' => $ext], [
                'code'       => $d['code'] ?? null,
                'name'       => $name,
                'serial'     => $d['serial'] ?? null,
                'location'   => $d['location'] ?? null,
                'department' => $d['department'] ?? null,
                'active'     => (bool) ($d['active'] ?? true),
            ]);
            $keep[] = $ext;
        }
        if ($keep) {
            QmsDevice::whereNotIn('ext_id', $keep)->update(['active' => false]);
        }

        // ===== Ngưỡng + thiết bị đo của module nhiệt độ/độ ẩm =====
        if ($env) {
            EnvSetting::first()->update(array_filter([
                'device_name'     => $env['deviceName'] ?? null,
                'location'        => $env['location'] ?? null,
                'serial'          => $env['serial'] ?? null,
                'reviewer'        => $env['reviewer'] ?? null,
                'temperature_min' => $env['temperatureMin'] ?? null,
                'temperature_max' => $env['temperatureMax'] ?? null,
                'humidity_min'    => $env['humidityMin'] ?? null,
                'humidity_max'    => $env['humidityMax'] ?? null,
                'time1'           => $env['time1'] ?? null,
                'time2'           => $env['time2'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''));
        }

        // ===== Biểu mẫu + danh mục rác thải =====
        if ($waste) {
            WasteSetting::first()->update(array_filter([
                'document_code'  => $waste['documentCode'] ?? null,
                'form_version'   => $waste['formVersion'] ?? null,
                'effective_date' => $waste['effectiveDate'] ?? null,
                'department'     => $waste['department'] ?? null,
                'year'           => $waste['year'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''));

            foreach (self::KINDS as $kind) {
                $list = $waste['catalogs'][$kind] ?? null;
                if (! is_array($list)) {
                    continue;
                }
                WasteCatalog::where('kind', $kind)->delete();
                foreach ($list as $i => $v) {
                    $v = trim((string) $v);
                    if ($v === '') {
                        continue;
                    }
                    WasteCatalog::create(['kind' => $kind, 'value' => $v, 'sort' => $i]);
                }
            }
        }

        // ===== Luồng nhập liệu =====
        if (is_array($flow)) {
            QmsOption::put('flow_auto_open', (bool) ($flow['autoOpen'] ?? true));
            QmsFlowStep::query()->delete();
            foreach ($flow['steps'] ?? [] as $i => $st) {
                $module = $st['module'] ?? '';
                $action = $st['action'] ?? '';
                if (! isset(QmsFlowController::ACTIONS[$module][$action])) {
                    continue;
                }
                QmsFlowStep::create([
                    'sort'   => $i,
                    'module' => $module,
                    'action' => $action,
                    'label'  => trim($st['label'] ?? '') ?: null,
                    'active' => (bool) ($st['active'] ?? true),
                ]);
            }
        }

        ActivityLogger::log('qms_config', 'Cập nhật cấu hình chung (nhân sự, phòng ban, thiết bị, ngưỡng, danh mục)');

        return response()->json(['ok' => true]);
    }

    /** Sinh mã mới không trùng, dạng u-n1 / dv-n1… */
    private function nextExtId(string $model, string $prefix): string
    {
        $i = 1;
        while ($model::where('ext_id', $prefix . $i)->exists()) {
            $i++;
        }
        return $prefix . $i;
    }
}
