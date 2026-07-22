<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 1 đợt nhập nhật ký rác thải. */
class WasteBatch extends Model
{
    protected $fillable = ['ext_id', 'department', 'note', 'created_by'];
}
