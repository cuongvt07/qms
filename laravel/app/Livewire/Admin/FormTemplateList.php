<?php

namespace App\Livewire\Admin;

use App\Models\DocumentCategory;
use App\Models\FormTemplate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Danh sách biểu mẫu có bộ lọc: tìm theo mã/tên, lọc theo mục TL, lọc theo trạng thái.
 */
class FormTemplateList extends Component
{
    use WithPagination;

    public string $q      = '';
    public string $catId  = '';
    public string $status = '';

    public function updatingQ(): void      { $this->resetPage(); }
    public function updatingCatId(): void  { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['q', 'catId', 'status']);
        $this->resetPage();
    }

    public function getCategoriesProperty()
    {
        return DocumentCategory::withCount('formTemplates')
            ->orderBy('ten_muc')
            ->get();
    }

    public function render()
    {
        $all = FormTemplate::with(['documentCategory', 'versions'])
            ->when($this->catId !== '', fn ($q) => $q->where('document_category_id', $this->catId))
            ->when($this->status !== '', fn ($q) => $q->where('trang_thai', $this->status))
            ->when(trim($this->q) !== '', function ($q) {
                $kw = '%' . trim($this->q) . '%';
                $q->where(fn ($w) => $w->where('ma_bm', 'like', $kw)->orWhere('ten_bm', 'like', $kw));
            })
            ->orderBy('ma_bm')
            ->get();

        // Nhóm theo Mục TL cho dễ tìm; nhóm chưa phân mục xuống cuối.
        $grouped = $all->groupBy(fn ($t) => $t->documentCategory->ten_muc ?? '~ Chưa phân mục')->sortKeys();

        return view('livewire.admin.form-template-list', [
            'grouped'    => $grouped,
            'total'      => $all->count(),
            'categories' => $this->categories,
        ])->layout('components.layouts.app');
    }
}
