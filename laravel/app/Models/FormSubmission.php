<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_template_version_id',
        'user_id',
        'ngay_nhap',
        'data_json',
        'trang_thai',
    ];

    protected $casts = [
        'data_json' => 'array',
        'ngay_nhap' => 'date',
    ];

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(FormTemplateVersion::class, 'form_template_version_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(FormSubmissionRow::class)->orderBy('row_index');
    }

    public function rowsForField(string $fieldKey): HasMany
    {
        return $this->hasMany(FormSubmissionRow::class)
            ->where('field_key', $fieldKey)
            ->orderBy('row_index');
    }

    public function isComplete(): bool
    {
        return $this->trang_thai === 'hoan_thanh';
    }
}
