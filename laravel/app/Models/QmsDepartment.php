<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Khoa / phòng ban dùng chung cho các module QMS. */
class QmsDepartment extends Model
{
    protected $fillable = ['name', 'active', 'sort'];
    protected $casts = ['active' => 'boolean'];
}
