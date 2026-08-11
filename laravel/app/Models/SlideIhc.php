<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Phiếu hóa mô miễn dịch gắn với một mã tiêu bản. */
class SlideIhc extends Model
{
    protected $table = 'slide_ihc';

    protected $casts = [
        'markers' => 'array', 'ngay_chi_dinh' => 'date', 'ngay_lay_mau' => 'date',
        'ngay_nhan_mau' => 'date', 'ngay_nhuom' => 'date', 'ngay_doc_kq' => 'date',
    ];

    protected $fillable = [
        'slide_code', 'patient_id', 'benh_nhan', 'nam_sinh', 'doi_tuong', 'khoa', 'vi_tri', 'cd_lam_sang',
        'so_block', 'markers', 'bs_chi_dinh', 'ngay_chi_dinh', 'ngay_lay_mau', 'ngay_nhan_mau',
        'ngay_nhuom', 'bs_doc_kq', 'ngay_doc_kq', 'trang_thai',
    ];
}
