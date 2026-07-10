<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_category_id',
        'ma_bm',
        'ten_bm',
        'file_goc_path',
        'trang_thai',
        'is_required',
        'raw_structure',
    ];

    protected $casts = [
        'raw_structure' => 'array',
        'is_required'   => 'boolean',
    ];

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FormTemplateVersion::class)->orderBy('version', 'desc');
    }

    public function latestVersion(): HasMany
    {
        return $this->hasMany(FormTemplateVersion::class)->latestOfMany('version');
    }

    public function getLatestVersionAttribute(): ?FormTemplateVersion
    {
        return $this->versions()->first();
    }

    public function dailyChecklists(): HasMany
    {
        return $this->hasMany(DailyChecklist::class);
    }
}
