<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 1 phát sinh trên thẻ kho: nhập / xuất / hủy / kiểm kê. */
class StockTransaction extends Model
{
    protected $fillable = [
        'ext_id', 'product_ext_id', 'date', 'type', 'qty', 'actual',
        'batch', 'expiry', 'destination', 'deliverer', 'receiver', 'note',
    ];
    protected $casts = ['date' => 'date', 'expiry' => 'date'];
}
