<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 1 bước trong luồng nhập liệu nối tiếp. */
class QmsFlowStep extends Model
{
    protected $fillable = ['sort', 'module', 'action', 'label', 'active'];
    protected $casts = ['active' => 'boolean'];
}
