<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Danh mục marker hóa mô miễn dịch. */
class IhcMarker extends Model
{
    public $timestamps = false;

    protected $casts = ['active' => 'boolean'];

    protected $fillable = ['ten', 'clone', 'hang', 'active'];
}
