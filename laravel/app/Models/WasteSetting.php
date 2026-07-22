<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Thông tin biểu mẫu của module nhật ký xử lý rác thải. */
class WasteSetting extends Model
{
    protected $fillable = ['document_code', 'form_version', 'effective_date', 'department', 'year'];
}
