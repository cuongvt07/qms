<?php

namespace App\Livewire\Admin;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Ổ tài liệu kiểu Google Drive (Giai đoạn 1 — nền tảng):
 *  - categoryId null  -> danh sách "ổ" (mỗi Mục tài liệu = 1 ổ).
 *  - categoryId có   -> duyệt thư mục/file trong ổ; folderId = thư mục đang mở (null = gốc).
 */
class DocumentDrive extends Component
{
    use WithFileUploads;

    public ?int $categoryId = null;
    public ?int $folderId   = null;
    public $uploads = [];

    public function getCategoryProperty(): ?DocumentCategory
    {
        return $this->categoryId ? DocumentCategory::find($this->categoryId) : null;
    }

    /** Danh sách ổ (khi chưa vào ổ nào). */
    public function getDrivesProperty()
    {
        return DocumentCategory::withCount(['documents as file_count' => fn ($q) => $q->where('type', 'file')])
            ->orderBy('ten_muc')->get();
    }

    /** Nội dung thư mục hiện tại: thư mục trước, file sau. */
    public function getItemsProperty()
    {
        if (! $this->categoryId) {
            return collect();
        }
        return Document::where('document_category_id', $this->categoryId)
            ->where('parent_id', $this->folderId)
            ->orderByRaw("CASE WHEN type='folder' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
    }

    /** Đường dẫn breadcrumb từ gốc ổ tới thư mục hiện tại. */
    public function getBreadcrumbProperty(): array
    {
        $crumb = [];
        $f = $this->folderId ? Document::find($this->folderId) : null;
        $guard = 0;
        while ($f && $guard++ < 50) {
            $crumb[] = ['id' => $f->id, 'name' => $f->name];
            $f = $f->parent;
        }
        return array_reverse($crumb);
    }

    public function openCategory(int $id): void
    {
        $this->categoryId = $id;
        $this->folderId   = null;
    }

    public function openFolder(int $id): void
    {
        $this->folderId = $id;
    }

    /** Nhảy tới 1 thư mục trong breadcrumb (null = gốc ổ). */
    public function goTo($id = null): void
    {
        $this->folderId = $id ? (int) $id : null;
    }

    public function exitDrive(): void
    {
        $this->categoryId = null;
        $this->folderId   = null;
    }

    /** Tạo ổ mới (= 1 Mục tài liệu). */
    public function createDrive($name): void
    {
        $name = trim((string) $name);
        if ($name === '') {
            return;
        }
        DocumentCategory::firstOrCreate(['ten_muc' => $name], ['is_active' => true]);
        session()->flash('drive_msg', 'Đã tạo ổ "' . $name . '".');
    }

    public function createFolder($name): void
    {
        $name = trim((string) $name);
        if ($name === '' || ! $this->categoryId) {
            return;
        }
        Document::create([
            'document_category_id' => $this->categoryId,
            'parent_id'            => $this->folderId,
            'type'                 => 'folder',
            'name'                 => $name,
            'uploaded_by'          => auth()->id(),
            'source'               => 'upload',
        ]);
        ActivityLogger::log('document', 'Tạo thư mục "' . $name . '" trong ổ tài liệu ' . ($this->category?->ten_muc ?? ''));
    }

    public function renameNode(int $id, $name): void
    {
        $name = trim((string) $name);
        $n = Document::find($id);
        if (! $n || $name === '' || $n->is_system) {
            return;
        }
        $n->update(['name' => $name]);
    }

    public function deleteNode(int $id): void
    {
        $n = Document::with('children')->find($id);
        if (! $n || $n->is_system) {
            return;
        }
        // Xoá file vật lý của cả cây con
        foreach ($this->collectFilePaths($n) as $p) {
            Storage::disk('local')->delete($p);
        }
        $name = $n->name;
        $n->delete();   // DB cascade xoá các node con
        ActivityLogger::log('document', 'Xoá "' . $name . '" khỏi ổ tài liệu ' . ($this->category?->ten_muc ?? ''));
    }

    private function collectFilePaths(Document $node): array
    {
        $paths = [];
        if ($node->type === 'file' && $node->path) {
            $paths[] = $node->path;
        }
        foreach ($node->children as $c) {
            $paths = array_merge($paths, $this->collectFilePaths($c));
        }
        return $paths;
    }

    /** Livewire tự gọi khi chọn/kéo-thả file -> lưu vào thư mục hiện tại. */
    public function updatedUploads(): void
    {
        if (! $this->categoryId) {
            return;
        }
        $this->validate([
            'uploads.*' => 'file|max:51200',   // 50MB/tệp (GĐ2 sẽ chunk cho file lớn)
        ], [], ['uploads.*' => 'tệp']);

        $n = 0;
        foreach ($this->uploads as $file) {
            $path = $file->store("documents/{$this->categoryId}", 'local');
            Document::create([
                'document_category_id' => $this->categoryId,
                'parent_id'            => $this->folderId,
                'type'                 => 'file',
                'name'                 => $file->getClientOriginalName(),
                'path'                 => $path,
                'mime'                 => $file->getMimeType(),
                'size'                 => $file->getSize(),
                'uploaded_by'          => auth()->id(),
                'source'               => 'upload',
            ]);
            $n++;
        }
        $this->uploads = [];
        if ($n) {
            ActivityLogger::log('document', "Tải lên {$n} tệp vào ổ tài liệu " . ($this->category?->ten_muc ?? ''));
            session()->flash('drive_msg', "Đã tải lên {$n} tệp.");
        }
    }

    public function render()
    {
        return view('livewire.admin.document-drive')->layout('components.layouts.app');
    }
}
