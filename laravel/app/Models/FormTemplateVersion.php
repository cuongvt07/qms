<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplateVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_template_id',
        'version',
        'schema_json',
        'duyet_boi',
        'ghi_chu',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'version'     => 'integer',
    ];

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'duyet_boi');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    /**
     * Lấy danh sách fields từ schema_json.
     */
    public function getFieldsAttribute(): array
    {
        return $this->schema_json['fields'] ?? [];
    }

    /**
     * Diff fields với version trước để cảnh báo admin khi cập nhật schema.
     * Trả về ['added' => [], 'removed' => [], 'changed' => []]
     */
    public function diffWithPrevious(): array
    {
        $previous = FormTemplateVersion::where('form_template_id', $this->form_template_id)
            ->where('version', $this->version - 1)
            ->first();

        if (! $previous) {
            return ['added' => $this->fields, 'removed' => [], 'changed' => []];
        }

        $oldKeys = collect($previous->fields)->keyBy('key');
        $newKeys = collect($this->fields)->keyBy('key');

        return [
            'added'   => $newKeys->diffKeys($oldKeys)->values()->toArray(),
            'removed' => $oldKeys->diffKeys($newKeys)->values()->toArray(),
            'changed' => $newKeys->filter(function ($field, $key) use ($oldKeys) {
                return $oldKeys->has($key) && $oldKeys[$key] !== $field;
            })->values()->toArray(),
        ];
    }
}
