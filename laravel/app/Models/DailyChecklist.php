<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_template_id',
        'user_id',
        'ngay_trong_tuan',
        'is_active',
    ];

    protected $casts = [
        'ngay_trong_tuan' => 'array',
        'is_active'       => 'boolean',
    ];

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kiểm tra xem hôm nay có cần nhập BM này không.
     */
    public function isDueToday(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (is_null($this->ngay_trong_tuan)) {
            return true; // Mỗi ngày
        }

        // ngay_trong_tuan: 1=T2, 2=T3, ..., 7=CN
        $today = now()->dayOfWeekIso; // 1=Monday ... 7=Sunday
        return in_array($today, $this->ngay_trong_tuan);
    }
}
