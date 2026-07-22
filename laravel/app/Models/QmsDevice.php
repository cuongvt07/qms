<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Trang thiết bị (module theo dõi khử nhiễm). */
class QmsDevice extends Model
{
    protected $fillable = ['ext_id', 'code', 'name', 'serial', 'location', 'department', 'active'];
    protected $casts = ['active' => 'boolean'];
}
