<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmissionRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_submission_id',
        'field_key',
        'row_index',
        'row_data_json',
    ];

    protected $casts = [
        'row_data_json' => 'array',
        'row_index'     => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }
}
