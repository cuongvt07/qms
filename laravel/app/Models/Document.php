<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 1 node trong ổ tài liệu: thư mục (folder) hoặc file.
 */
class Document extends Model
{
    protected $fillable = [
        'document_category_id', 'parent_id', 'type', 'name', 'path',
        'mime', 'size', 'uploaded_by', 'source', 'form_submission_id', 'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'size'      => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Document::class, 'parent_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    /** Kích thước dễ đọc. */
    public function humanSize(): string
    {
        $b = (int) $this->size;
        if ($b <= 0) {
            return '—';
        }
        $u = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($b, 1024));
        $i = min($i, count($u) - 1);
        return round($b / (1024 ** $i), $i ? 1 : 0) . ' ' . $u[$i];
    }

    /** Nhóm loại file để chọn icon. */
    public function kind(): string
    {
        if ($this->isFolder()) {
            return 'folder';
        }
        $m = (string) $this->mime;
        $ext = strtolower(pathinfo((string) $this->name, PATHINFO_EXTENSION));
        if (str_starts_with($m, 'image/')) {
            return 'image';
        }
        if ($m === 'application/pdf' || $ext === 'pdf') {
            return 'pdf';
        }
        if (in_array($ext, ['doc', 'docx'], true) || str_contains($m, 'word')) {
            return 'word';
        }
        if (in_array($ext, ['xls', 'xlsx', 'csv'], true) || str_contains($m, 'sheet') || str_contains($m, 'excel')) {
            return 'excel';
        }
        return 'file';
    }
}
