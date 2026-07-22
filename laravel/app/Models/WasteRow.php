<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** 1 dòng nhật ký xử lý rác thải. */
class WasteRow extends Model
{
    protected $fillable = [
        'ext_id', 'batch_ext_id', 'date', 'time', 'waste_type',
        'treatment', 'location', 'performer_ext_id', 'note', 'rev',
    ];
    protected $casts = ['date' => 'date'];
}
