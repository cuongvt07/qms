<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Ghi nhật ký hoạt động (audit log). Gọi ActivityLogger::log(...) ở các điểm nghiệp vụ.
 * Không bao giờ để lỗi log làm hỏng thao tác chính.
 */
class ActivityLogger
{
    public static function log(string $action, ?string $description = null, ?Model $subject = null, array $props = []): void
    {
        try {
            ActivityLog::create([
                'user_id'      => Auth::id(),
                'session_id'   => session()->getId(),
                'action'       => $action,
                'description'  => $description,
                'subject_type' => $subject ? class_basename($subject) : null,
                'subject_id'   => $subject?->getKey(),
                'properties'   => $props ?: null,
                'ip_address'   => request()->ip(),
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // nuốt lỗi — log không được phép chặn nghiệp vụ
        }
    }
}
