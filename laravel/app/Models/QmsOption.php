<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tuỳ chọn chung dạng khoá/giá trị. */
class QmsOption extends Model
{
    protected $fillable = ['key', 'value'];
    protected $casts = ['value' => 'array'];

    public static function val(string $key, $default = null)
    {
        return static::where('key', $key)->first()->value ?? $default;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
