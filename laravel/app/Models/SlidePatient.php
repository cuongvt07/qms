<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Bệnh nhân — một người có thể có nhiều mã tiêu bản. */
class SlidePatient extends Model
{
    protected $fillable = ['ma_bn', 'ho_ten', 'nam_sinh', 'doi_tuong', 'khoa'];
}
