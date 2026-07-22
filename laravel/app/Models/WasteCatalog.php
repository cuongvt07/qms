<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Danh mục chọn nhanh (loại chất thải / biện pháp xử lý / vị trí). */
class WasteCatalog extends Model
{
    protected $fillable = ['kind', 'value', 'sort'];
}
