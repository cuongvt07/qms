<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 1 dòng nhật ký theo dõi nhiệt độ, độ ẩm, vệ sinh. */
class EnvRecord extends Model
{
    protected $fillable = [
        'ext_id', 'date', 'inspector_ext_id', 'temperature1', 'temperature2',
        'humidity1', 'humidity2', 'cleaning', 'remedy', 'rev',
    ];
    protected $casts = ['date' => 'date'];
}
