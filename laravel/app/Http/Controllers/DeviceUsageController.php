<?php

namespace App\Http\Controllers;

use App\Models\DeviceUsageClosure;
use App\Models\DeviceUsageLog;
use App\Models\QmsDevice;
use App\Models\QmsStaff;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module nhật ký sử dụng thiết bị.
 * Thiết bị và nhân sự lấy từ Cấu hình chung; mỗi ngày của mỗi thiết bị là 1 dòng nhật ký.
 */
class DeviceUsageController extends Controller
{
    /** Thiết bị mẫu của biểu mẫu này (chỉ nạp khi danh mục chưa có). */
    private const SEED_DEVICES = [
        ['dv-cda', 'GPB.CDA 01', 'Chậu dàn tiêu bản', '37047', 'Phòng cắt Block', 4],
        ['dv-bpt', 'GPB.BPT 02', 'Hệ thống phẫu tích bệnh phẩm', 'Chưa cập nhật', 'Phòng phẫu tích BP', 8],
        ['dv-bsa', 'GPB.BSA 01', 'Bàn sấy tiêu bản MH6616', '10720131', 'Phòng chọc hút tế bào', 8],
        ['dv-khv', 'GPB.KHV 09', 'Kính hiển vi quang học nghiên cứu BX43', '9H50967', 'Buồng đọc KQ', 8],
    ];

    public function page()
    {
        return view('modules.device-usage');
    }

    private function seedIfNeeded(): void
    {
        foreach (self::SEED_DEVICES as [$ext, $code, $name, $serial, $location, $hours]) {
            if (QmsDevice::where('ext_id', $ext)->orWhere('code', $code)->exists()) {
                continue;
            }
            QmsDevice::create([
                'ext_id' => $ext, 'code' => $code, 'name' => $name, 'serial' => $serial,
                'location' => $location, 'department' => 'Giải phẫu bệnh lý',
                'default_hours' => $hours, 'active' => true,
            ]);
        }
    }

    /** Trạng thái cho giao diện (đúng cấu trúc mẫu thiết kế). */
    public function state(): JsonResponse
    {
        $this->seedIfNeeded();

        $records = [];
        foreach (DeviceUsageLog::orderBy('date')->get() as $r) {
            $k = $r->device_ext_id . '-' . $r->date->format('Y-m');
            $records[$k][] = [
                'id'          => (string) $r->id,
                'date'        => $r->date->toDateString(),
                'user'        => $r->user_name ?? '',
                'hours'       => $r->hours === null ? '' : (float) $r->hours,
                'condition'   => $r->condition ?? '',
                'note'        => $r->note ?? '',
                'status'      => $r->status,
                'confirmedAt' => $r->confirmed_at ?? '',
                'version'     => $r->rev,
            ];
        }

        $closed = [];
        foreach (DeviceUsageClosure::get() as $c) {
            $closed[$c->device_ext_id . '-' . $c->month] = ['at' => $c->closed_at, 'by' => $c->closed_by];
        }

        return response()->json([
            'devices' => QmsDevice::where('active', true)->orderBy('id')->get()->map(fn ($d) => [
                'id'            => $d->ext_id,
                'code'          => $d->code ?? $d->ext_id,
                'name'          => $d->name,
                'serial'        => $d->serial ?? 'Chưa cập nhật',
                'location'      => $d->location ?? '',
                'defaultHours'  => (float) $d->default_hours,
            ])->all(),
            'users'        => QmsStaff::where('active', true)->orderBy('id')->pluck('name')->all(),
            'records'      => (object) $records,
            'closedMonths' => (object) $closed,
        ]);
    }

    /** Lưu toàn bộ nhật ký + trạng thái chốt sổ. */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'records'      => 'array',
            'closedMonths' => 'array',
            'devices'      => 'array',
        ]);
        $records = $request->input('records', []);
        $closed  = $request->input('closedMonths', []);
        $devices = $request->input('devices', []);

        // số giờ tiêu chuẩn có thể sửa trong "Thiết lập thiết bị"
        foreach ($devices as $d) {
            if (empty($d['id'])) {
                continue;
            }
            QmsDevice::where('ext_id', $d['id'])->update(['default_hours' => $d['defaultHours'] ?? 8]);
        }

        $keep = [];
        foreach ($records as $key => $rows) {
            // key = "<deviceExtId>-YYYY-MM" -> 8 ký tự cuối là "-YYYY-MM"
            $device = substr($key, 0, -8);
            foreach ((array) $rows as $r) {
                if (empty($r['date'])) {
                    continue;
                }
                $log = DeviceUsageLog::updateOrCreate(
                    ['device_ext_id' => $device, 'date' => $r['date']],
                    [
                        'user_name'    => $r['user'] ?? null,
                        'hours'        => ($r['hours'] ?? '') === '' ? null : $r['hours'],
                        'condition'    => $r['condition'] ?? null,
                        'note'         => $r['note'] ?? null,
                        'status'       => $r['status'] ?? 'pending',
                        'confirmed_at' => $r['confirmedAt'] ?? null,
                        'rev'          => $r['version'] ?? 1,
                    ]
                );
                $keep[] = $log->id;
            }
        }
        if ($keep) {
            DeviceUsageLog::whereNotIn('id', $keep)->delete();
        }

        DeviceUsageClosure::query()->delete();
        foreach ($closed as $key => $info) {
            $device = substr($key, 0, -8);
            $month  = substr($key, -7);
            DeviceUsageClosure::create([
                'device_ext_id' => $device,
                'month'         => $month,
                'closed_at'     => $info['at'] ?? null,
                'closed_by'     => $info['by'] ?? null,
            ]);
        }

        ActivityLogger::log('device_usage', 'Cập nhật nhật ký sử dụng thiết bị');

        return response()->json(['ok' => true]);
    }
}
