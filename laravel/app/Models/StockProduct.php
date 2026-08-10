<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Thẻ kho của 1 mã hàng (1 mã hàng = 1 thẻ kho, card_no tự sinh). */
class StockProduct extends Model
{
    protected $casts = ['active' => 'boolean', 'expiry' => 'date'];

    protected $fillable = [
        'ext_id', 'card_no', 'code', 'name', 'group_name', 'unit', 'packing', 'supplier',
        'expiry', 'min_qty', 'max_qty', 'active', 'note', 'image_path',
    ];
}
