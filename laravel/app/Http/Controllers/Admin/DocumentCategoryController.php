<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentCategoryController extends Controller
{
    public function index()
    {
        $categories = DocumentCategory::withCount('formTemplates')->orderBy('ten_muc')->get();
        return view('admin.document-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.document-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_muc' => 'required|string|max:255|unique:document_categories,ten_muc',
            'mo_ta'   => 'nullable|string|max:1000',
        ]);

        DocumentCategory::create($validated);

        return redirect()->route('admin.document-categories.index')
            ->with('success', 'Đã tạo mục tài liệu.');
    }

    public function edit(DocumentCategory $documentCategory)
    {
        return view('admin.document-categories.edit', compact('documentCategory'));
    }

    public function update(Request $request, DocumentCategory $documentCategory)
    {
        $validated = $request->validate([
            'ten_muc' => 'required|string|max:255|unique:document_categories,ten_muc,' . $documentCategory->id,
            'mo_ta'   => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $documentCategory->update($validated);

        return redirect()->route('admin.document-categories.index')
            ->with('success', 'Đã cập nhật mục tài liệu.');
    }

    public function destroy(DocumentCategory $documentCategory)
    {
        if ($documentCategory->formTemplates()->count() > 0) {
            return back()->with('error', 'Không thể xóa mục đang có biểu mẫu.');
        }

        $documentCategory->delete();
        return redirect()->route('admin.document-categories.index')
            ->with('success', 'Đã xóa mục tài liệu.');
    }
}
