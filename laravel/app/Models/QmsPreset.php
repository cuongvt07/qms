<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Mẫu mặc định cho các form nhập nhiều của module QMS. */
class QmsPreset extends Model
{
    protected $fillable = ['module', 'preset_key', 'payload', 'updated_by'];
    protected $casts = ['payload' => 'array'];
}
