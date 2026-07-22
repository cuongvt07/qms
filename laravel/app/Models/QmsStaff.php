<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Nhân sự dùng chung cho các module QMS (người kiểm tra / thực hiện). */
class QmsStaff extends Model
{
    protected $table = 'qms_staff';
    protected $fillable = ['ext_id', 'name', 'role', 'active'];
    protected $casts = ['active' => 'boolean'];
}
