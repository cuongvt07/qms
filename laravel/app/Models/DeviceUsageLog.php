<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 1 ngày trong nhật ký sử dụng thiết bị. */
class DeviceUsageLog extends Model
{
    protected $fillable = [
        'device_ext_id', 'date', 'user_name', 'hours', 'condition',
        'note', 'status', 'confirmed_at', 'rev',
    ];
    protected $casts = ['date' => 'date'];
}
