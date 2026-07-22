<?php

namespace App\Http\Controllers;

use App\Models\EnvRecord;
use App\Models\EnvSetting;
use App\Models\QmsStaff;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module theo dõi nhiệt độ, độ ẩm và vệ sinh phòng xét nghiệm.
 * Giao diện giữ nguyên mẫu thiết kế; tầng dữ liệu chuyển từ localStorage sang CSDL qua 2 API:
 *   GET  state  -> trả đúng cấu trúc JSON mà giao diện đang dùng
 *   POST state  -> lưu lại (đồng bộ settings + nhân sự + toàn bộ dòng nhật ký)
 */
class EnvMonitorController extends Controller
{
    /** Nhân sự mặc định (theo mẫu thiết kế) khi CSDL còn trống. */
    private const SEED_STAFF = [
        ['u-admin', 'Quản trị hệ thống', 'admin'],
        ['u-1', 'KTV Bùi Thị Thái', 'technician'],
        ['u-2', 'KTV Khổng Thanh Thủy', 'technician'],
        ['u-3', 'KTV Lê Minh Sơn', 'technician'],
        ['u-4', 'KTV Lê Thị Quyến', 'technician'],
        ['u-5', 'KTV Nguyễn Ngọc Hà', 'technician'],
        ['u-6', 'KTV Nguyễn Quang Thi', 'technician'],
        ['u-7', 'KTV Nguyễn Thị Huệ', 'technician'],
        ['u-8', 'KTV Nguyễn Thị Hải Yến', 'technician'],
        ['u-9', 'KTV Vũ Quang Đức', 'technician'],
        ['u-10', 'KTV Đinh Ngọc Minh Anh', 'technician'],
    ];

    public function page()
    {
        return view('modules.env-monitor');
    }

    private function settings(): EnvSetting
    {
        $s = EnvSetting::first();
        if (! $s) {
            $s = EnvSetting::create([
                'device_name' => 'NHIỆT ẨM KẾ TT-513',
                'location'    => 'Phòng chọc hút tế bào',
                'serial'      => 'A216',
                'reviewer'    => 'ThS. Nguyễn Thị Ngọc Ánh',
            ]);
        }
        if (QmsStaff::count() === 0) {
            foreach (self::SEED_STAFF as [$ext, $name, $role]) {
                QmsStaff::create(['ext_id' => $ext, 'name' => $name, 'role' => $role]);
            }
        }
        return $s;
    }

    /** Trạng thái cho giao diện (đúng cấu trúc mẫu thiết kế). */
    public function state(): JsonResponse
    {
        $s = $this->settings();

        return response()->json([
            'version'  => 1,
            'metadata' => [
                'title'      => $s->title,
                'deviceName' => $s->device_name,
                'location'   => $s->location,
                'serial'     => $s->serial,
                'reviewer'   => $s->reviewer,
            ],
            'settings' => [
                'temperatureMin'   => (float) $s->temperature_min,
                'temperatureMax'   => (float) $s->temperature_max,
                'humidityMin'      => (float) $s->humidity_min,
                'humidityMax'      => (float) $s->humidity_max,
                'measurementTime1' => $s->time1,
                'measurementTime2' => $s->time2,
            ],
            'users' => QmsStaff::where('active', true)->orderBy('id')
                ->get()->map(fn ($u) => ['id' => $u->ext_id, 'name' => $u->name, 'role' => $u->role])->all(),
            'records' => EnvRecord::orderBy('date', 'desc')->orderBy('id', 'desc')->get()->map(fn ($r) => [
                'id'           => $r->ext_id,
                'date'         => $r->date?->toDateString(),
                'inspectorId'  => $r->inspector_ext_id,
                'temperature1' => $r->temperature1 === null ? null : (float) $r->temperature1,
                'temperature2' => $r->temperature2 === null ? null : (float) $r->temperature2,
                'humidity1'    => $r->humidity1 === null ? null : (float) $r->humidity1,
                'humidity2'    => $r->humidity2 === null ? null : (float) $r->humidity2,
                'cleaning'     => $r->cleaning ?? '',
                'remedy'       => $r->remedy ?? '',
                'createdAt'    => optional($r->created_at)->toIso8601String(),
                'updatedAt'    => optional($r->updated_at)->toIso8601String(),
                'version'      => $r->rev,
                'history'      => [],
            ])->all(),
            'currentUserId' => 'u-admin',
        ]);
    }

    /** Lưu toàn bộ trạng thái từ giao diện xuống CSDL. */
    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'metadata'        => 'array',
            'settings'        => 'array',
            'users'           => 'array',
            'records'         => 'array',
            'records.*.id'    => 'required|string|max:60',
            'records.*.date'  => 'nullable|date',
        ]);

        $s  = $this->settings();
        $md = $data['metadata'] ?? [];
        $st = $data['settings'] ?? [];
        $s->update(array_filter([
            'title'           => $md['title'] ?? null,
            'device_name'     => $md['deviceName'] ?? null,
            'location'        => $md['location'] ?? null,
            'serial'          => $md['serial'] ?? null,
            'reviewer'        => $md['reviewer'] ?? null,
            'temperature_min' => $st['temperatureMin'] ?? null,
            'temperature_max' => $st['temperatureMax'] ?? null,
            'humidity_min'    => $st['humidityMin'] ?? null,
            'humidity_max'    => $st['humidityMax'] ?? null,
            'time1'           => $st['measurementTime1'] ?? null,
            'time2'           => $st['measurementTime2'] ?? null,
        ], fn ($v) => $v !== null));

        // Nhân sự
        if (! empty($data['users'])) {
            $keep = [];
            foreach ($data['users'] as $u) {
                if (empty($u['id'])) {
                    continue;
                }
                QmsStaff::updateOrCreate(['ext_id' => $u['id']], ['name' => $u['name'] ?? $u['id'], 'role' => $u['role'] ?? 'technician', 'active' => true]);
                $keep[] = $u['id'];
            }
            QmsStaff::whereNotIn('ext_id', $keep)->update(['active' => false]);
        }

        // Dòng nhật ký
        $rows = $data['records'] ?? [];
        $keep = [];
        foreach ($rows as $r) {
            EnvRecord::updateOrCreate(['ext_id' => $r['id']], [
                'date'             => $r['date'] ?? null,
                'inspector_ext_id' => $r['inspectorId'] ?? null,
                'temperature1'     => $r['temperature1'] ?? null,
                'temperature2'     => $r['temperature2'] ?? null,
                'humidity1'        => $r['humidity1'] ?? null,
                'humidity2'        => $r['humidity2'] ?? null,
                'cleaning'         => $r['cleaning'] ?? null,
                'remedy'           => $r['remedy'] ?? null,
                'rev'              => $r['version'] ?? 1,
            ]);
            $keep[] = $r['id'];
        }
        EnvRecord::whereNotIn('ext_id', $keep ?: ['__none__'])->delete();

        ActivityLogger::log('env_monitor', 'Cập nhật nhật ký nhiệt độ/độ ẩm/vệ sinh (' . count($rows) . ' dòng)');

        return response()->json(['ok' => true, 'records' => count($rows)]);
    }
}
