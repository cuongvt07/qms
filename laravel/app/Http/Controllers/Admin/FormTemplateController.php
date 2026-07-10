<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormTemplateController extends Controller
{
    /** Tải file .docx mẫu (có placeholder) để mở/soi trong Word. */
    public function downloadTemplate(FormTemplate $template)
    {
        abort_unless($template->file_goc_path && Storage::disk('local')->exists($template->file_goc_path), 404, 'Chưa có file mẫu.');

        $name = \Illuminate\Support\Str::slug($template->ma_bm ?: 'bieu-mau') . '.docx';
        return Storage::disk('local')->download($template->file_goc_path, $name);
    }

    public function index()
    {
        $templates = FormTemplate::with(['documentCategory', 'versions'])
            ->orderBy('trang_thai')
            ->orderBy('ma_bm')
            ->paginate(20);

        return view('admin.form-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.form-templates.create');
    }

    public function review(FormTemplate $template)
    {
        return view('admin.form-templates.review', compact('template'));
    }

    public function edit(FormTemplate $template)
    {
        $categories = DocumentCategory::where('is_active', true)->orderBy('ten_muc')->get();
        return view('admin.form-templates.edit', compact('template', 'categories'));
    }

    public function update(Request $request, FormTemplate $template)
    {
        $validated = $request->validate([
            'document_category_id' => 'required|exists:document_categories,id',
            'ma_bm'                => 'required|string|max:100|unique:form_templates,ma_bm,' . $template->id,
            'ten_bm'               => 'required|string|max:255',
            'trang_thai'           => 'required|in:draft,active,archived',
            'is_required'          => 'boolean',
        ]);
        $validated['is_required'] = $request->boolean('is_required');

        $template->update($validated);

        return redirect()->route('admin.form-templates.index')
            ->with('success', 'Đã cập nhật biểu mẫu ' . $template->ma_bm);
    }

    public function destroy(FormTemplate $template)
    {
        // Chỉ xóa được template ở trạng thái draft
        if ($template->trang_thai !== 'draft') {
            return back()->with('error', 'Chỉ có thể xóa biểu mẫu ở trạng thái Draft.');
        }

        $template->delete();
        return redirect()->route('admin.form-templates.index')
            ->with('success', 'Đã xóa biểu mẫu ' . $template->ma_bm);
    }
}
