<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = ['ten_muc', 'mo_ta', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function formTemplates(): HasMany
    {
        return $this->hasMany(FormTemplate::class);
    }

    public function activeFormTemplates(): HasMany
    {
        return $this->hasMany(FormTemplate::class)->where('trang_thai', 'active');
    }
}
