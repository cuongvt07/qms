<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Chốt sổ 1 tháng của 1 thiết bị. */
class DeviceUsageClosure extends Model
{
    protected $fillable = ['device_ext_id', 'month', 'closed_at', 'closed_by'];
}
