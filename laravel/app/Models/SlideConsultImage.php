<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Ảnh đính kèm hội chẩn — bị xóa khi chốt kết luận. */
class SlideConsultImage extends Model
{
    protected $fillable = ['consult_id', 'path', 'ten_goc', 'nguoi_tai'];
}
