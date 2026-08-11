<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Một mã tiêu bản trong sổ soạn (26C2472). Một mã có nhiều block và nhiều lam. */
class SlideRecord extends Model
{
    protected $casts = ['ngay_soan' => 'date', 'ngay_doc' => 'date', 'da_doc' => 'boolean'];

    protected $fillable = [
        'code', 'yy', 'letter', 'seq', 'patient_id', 'so_block', 'so_tieu_ban', 'ngay_soan', 'gia_so',
        'ktv_cat', 'ktv_soan', 'bs_doc', 'ket_qua', 'da_doc', 'ngay_doc', 'ghi_chu',
    ];
}
