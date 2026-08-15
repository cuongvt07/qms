<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Một mã tiêu bản trong sổ soạn (26C2472). Một mã có nhiều block và nhiều lam. */
class SlideRecord extends Model
{
    protected $casts = [
        'ngay_soan' => 'date', 'ngay_doc' => 'date', 'ngay_nhan' => 'date', 'ngay_hen' => 'date',
        'da_doc' => 'boolean',
    ];

    protected $fillable = [
        'code', 'yy', 'letter', 'seq', 'patient_id', 'so_block', 'so_tieu_ban', 'ngay_soan', 'ngay_hen', 'gia_so',
        'ktv_cat', 'ktv_soan', 'bs_doc', 'ket_qua', 'da_doc', 'trang_thai_doc', 'ngay_nhan', 'ngay_doc', 'ghi_chu',
    ];

    /** Ba trạng thái đọc: chưa đọc → đã nhận → đã đọc. da_doc giữ đồng bộ cho các màn cũ. */
    public function datTrangThaiDoc(string $tt, ?string $bs = null): void
    {
        $hom = now()->toDateString();
        $this->trang_thai_doc = $tt;
        $this->da_doc         = $tt === 'doc';
        $this->ngay_doc       = $tt === 'doc' ? ($this->ngay_doc?->toDateString() ?: $hom) : null;
        $this->ngay_nhan      = $tt === 'chua' ? null : ($this->ngay_nhan?->toDateString() ?: $hom);
        if ($bs) {
            $this->bs_doc = $bs;
        }
    }
}
