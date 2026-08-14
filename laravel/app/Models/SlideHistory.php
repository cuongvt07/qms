<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Một lượt đã hoàn tất của mã tiêu bản — mã được trả về sổ soạn để dùng lại sau khi chốt. */
class SlideHistory extends Model
{
    protected $table = 'slide_history';

    protected $casts = [
        'markers'       => 'array',
        'hmmd'          => 'array',
        'hoi_chan'      => 'array',
        'ngay_soan'     => 'date',
        'ngay_nhan'     => 'date',
        'ngay_doc'      => 'date',
        'ngay_doc_hmmd' => 'date',
        'ngay_chot'     => 'date',
    ];

    protected $guarded = ['id'];
}
