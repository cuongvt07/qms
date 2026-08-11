<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Một ý kiến của bác sĩ trong phiên hội chẩn. */
class SlideConsultNote extends Model
{
    protected $fillable = ['consult_id', 'bs', 'noi_dung'];
}
