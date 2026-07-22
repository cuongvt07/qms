<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Cấu hình + thông tin thiết bị của module Nhiệt độ/độ ẩm/vệ sinh (1 dòng duy nhất). */
class EnvSetting extends Model
{
    protected $fillable = [
        'title', 'device_name', 'location', 'serial', 'reviewer',
        'temperature_min', 'temperature_max', 'humidity_min', 'humidity_max', 'time1', 'time2',
    ];
}
