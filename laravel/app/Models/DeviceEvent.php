<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 1 sự kiện khử nhiễm / bảo dưỡng / sửa chữa của thiết bị. */
class DeviceEvent extends Model
{
    protected $fillable = [
        'ext_id', 'date', 'device_ext_id', 'activity_type', 'reason',
        'condition', 'condition_text', 'note', 'performed_by', 'created_by', 'rev',
    ];
    protected $casts = ['date' => 'date'];
}
