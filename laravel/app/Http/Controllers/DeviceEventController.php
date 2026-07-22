<?php

namespace App\Http\Controllers;

use App\Models\DeviceEvent;
use App\Models\QmsDevice;
use App\Models\QmsStaff;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module theo dõi khử nhiễm trang thiết bị.
 * Giao diện giữ nguyên mẫu thiết kế; localStorage được thay bằng 2 API state / save.
 */
class DeviceEventController extends Controller
{
    private const MODULE = 'device';

    public function page()
    {
        return view('modules.device-events');
    }

    /** Nạp danh mục thiết bị + nhân sự + nhật ký mẫu ở lần chạy đầu tiên. */
    private function seedIfEmpty(): void
    {
        if (QmsDevice::count() > 0 || QmsStaff::where('module', self::MODULE)->count() > 0) {
            return;
        }
        $file = database_path('seed/device_seed.json');
        if (! is_file($file)) {
            return;
        }
        $seed = json_decode(file_get_contents($file), true) ?: [];

        foreach ($seed['devices'] ?? [] as $d) {
            QmsDevice::updateOrCreate(['ext_id' => $d['id']], [
                'code'       => $d['code'] ?? null,
                'name'       => $d['name'] ?? '',
                'serial'     => $d['serial'] ?? null,
                'location'   => $d['location'] ?? null,
                'department' => $d['department'] ?? null,
                'active'     => (bool) ($d['active'] ?? true),
            ]);
        }
        foreach ($seed['users'] ?? [] as $u) {
            QmsStaff::updateOrCreate(['ext_id' => $u['id']], [
                'module' => 'all',
                'name'   => $u['name'] ?? $u['id'],
                'role'   => $u['role'] ?? 'technician',
            ]);
        }
        foreach ($seed['events'] ?? [] as $e) {
            DeviceEvent::updateOrCreate(['ext_id' => $e['id']], [
                'date'           => $e['date'] ?? null,
                'device_ext_id'  => $e['deviceId'] ?? null,
                'activity_type'  => $e['activityType'] ?? 'other',
                'reason'         => $e['reason'] ?? null,
                'condition'      => $e['condition'] ?? 'pending',
                'condition_text' => $e['conditionText'] ?? null,
                'note'           => $e['note'] ?? null,
                'performed_by'   => $e['performedBy'] ?? null,
                'created_by'     => $e['createdBy'] ?? null,
                'rev'            => $e['version'] ?? 1,
            ]);
        }
    }

    /** Trạng thái cho giao diện (đúng cấu trúc mẫu thiết kế). */
    public function state(): JsonResponse
    {
        $this->seedIfEmpty();

        return response()->json([
            'version' => 1,
            'devices' => QmsDevice::orderBy('id')->get()->map(fn ($d) => [
                'id'         => $d->ext_id,
                'code'       => $d->code,
                'name'       => $d->name,
                'serial'     => $d->serial,
                'location'   => $d->location,
                'department' => $d->department,
                'active'     => (bool) $d->active,
            ])->all(),
            'users' => QmsStaff::where('active', true)->orderBy('id')
                ->get()->map(fn ($u) => ['id' => $u->ext_id, 'name' => $u->name, 'role' => $u->role])->all(),
            'events' => DeviceEvent::orderBy('date', 'desc')->orderBy('id', 'desc')->get()->map(fn ($e) => [
                'id'            => $e->ext_id,
                'date'          => $e->date?->toDateString(),
                'deviceId'      => $e->device_ext_id,
                'activityType'  => $e->activity_type,
                'reason'        => $e->reason ?? '',
                'condition'     => $e->condition,
                'conditionText' => $e->condition_text ?? '',
                'note'          => $e->note ?? '',
                'performedBy'   => $e->performed_by ?? '',
                'createdBy'     => $e->created_by,
                'createdAt'     => optional($e->created_at)->toIso8601String(),
                'updatedAt'     => optional($e->updated_at)->toIso8601String(),
                'version'       => $e->rev,
                'history'       => [],
            ])->all(),
            'currentUserId' => 'u-admin',
        ]);
    }

    /** Lưu toàn bộ trạng thái từ giao diện xuống CSDL. */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'devices'       => 'array',
            'users'         => 'array',
            'events'        => 'array',
            'events.*.id'   => 'required|string|max:60',
            'events.*.date' => 'nullable|date',
        ]);
        // validate() chỉ trả field đã khai báo -> đọc payload thô để không mất cột.
        $devices = $request->input('devices', []);
        $users   = $request->input('users', []);
        $events  = $request->input('events', []);

        foreach ($devices as $d) {
            if (empty($d['id'])) {
                continue;
            }
            QmsDevice::updateOrCreate(['ext_id' => $d['id']], [
                'code'       => $d['code'] ?? null,
                'name'       => $d['name'] ?? '',
                'serial'     => $d['serial'] ?? null,
                'location'   => $d['location'] ?? null,
                'department' => $d['department'] ?? null,
                'active'     => (bool) ($d['active'] ?? true),
            ]);
        }


        $keep = [];
        foreach ($events as $e) {
            DeviceEvent::updateOrCreate(['ext_id' => $e['id']], [
                'date'           => $e['date'] ?? null,
                'device_ext_id'  => $e['deviceId'] ?? null,
                'activity_type'  => $e['activityType'] ?? 'other',
                'reason'         => $e['reason'] ?? null,
                'condition'      => $e['condition'] ?? 'pending',
                'condition_text' => $e['conditionText'] ?? null,
                'note'           => $e['note'] ?? null,
                'performed_by'   => $e['performedBy'] ?? null,
                'created_by'     => $e['createdBy'] ?? null,
                'rev'            => $e['version'] ?? 1,
            ]);
            $keep[] = $e['id'];
        }
        DeviceEvent::whereNotIn('ext_id', $keep ?: ['__none__'])->delete();

        ActivityLogger::log('device_events', 'Cập nhật nhật ký khử nhiễm trang thiết bị (' . count($events) . ' sự kiện)');

        return response()->json(['ok' => true, 'events' => count($events)]);
    }
}
