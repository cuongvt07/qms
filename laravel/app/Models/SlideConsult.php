<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Phiên hội chẩn của một mã tiêu bản. */
class SlideConsult extends Model
{
    protected $casts = ['ngay_chot' => 'date'];

    protected $fillable = ['slide_code', 'ket_luan', 'bs_chot', 'ngay_chot'];

    public function notes(): HasMany
    {
        return $this->hasMany(SlideConsultNote::class, 'consult_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(SlideConsultImage::class, 'consult_id');
    }
}
