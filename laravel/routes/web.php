<?php

use App\Http\Controllers\Admin\DocumentCategoryController;
use App\Http\Controllers\Admin\FormTemplateController;
use App\Http\Controllers\FormSubmissionController;
use App\Livewire\Dashboard;
use App\Livewire\DynamicFormRenderer;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('dashboard'));

// Auth (Laravel Breeze/Fortify sẽ đăng ký các route login/logout)
require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {

    // Dashboard nhắc việc hàng ngày
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Nhập liệu form — sổ đăng ký nhiều ngày (màn chính khi mở 1 biểu mẫu)
    Route::get('/forms/{versionId}', \App\Livewire\RegisterFill::class)->name('forms.register');

    // Lịch nhập theo ngày của 1 biểu mẫu
    Route::get('/forms/calendar/{versionId}', \App\Livewire\FormCalendar::class)->name('forms.calendar');

    // Điền trực tiếp trên giao diện giống bản gốc (docx-preview render trình duyệt)
    Route::get('/forms/inline/{versionId}', \App\Livewire\InlineFill::class)->name('forms.inline');
    Route::get('/forms/inline/{versionId}/config', \App\Livewire\InlineFill::class)->name('forms.inline-config');
    Route::get('/forms/inline/{versionId}/source', [FormSubmissionController::class, 'sourceDocx'])
        ->name('forms.inline-source');
    Route::get('/forms/inline-export/{submission}', [FormSubmissionController::class, 'inlineExport'])
        ->name('forms.inline-export');
    Route::get('/forms/attachment/{attachment}', [FormSubmissionController::class, 'attachment'])
        ->name('forms.attachment');

    // Nhập liệu 1 ngày (đủ field, gồm cả bảng lặp) — mở từ sổ khi cần chi tiết
    Route::get('/forms/fill/{versionId}', function ($versionId, \Illuminate\Http\Request $request) {
        return view('forms.fill', [
            'versionId' => (int) $versionId,
            'date'      => $request->query('date', now()->toDateString()),
        ]);
    })->name('forms.fill');

    // Export .docx đã điền
    Route::get('/forms/export/{submission}', [FormSubmissionController::class, 'export'])
        ->name('forms.export');

    // Tải file mẫu gốc (.docx có placeholder)
    Route::get('/forms/template/{template}/download', [FormTemplateController::class, 'downloadTemplate'])
        ->name('forms.export-template');

    // Khu quản trị — 1 role: mọi người đăng nhập đều dùng được
    // (giữ prefix 'admin' + name 'admin.' để không phải đổi route() khắp nơi)
    Route::prefix('admin')->name('admin.')->group(function () {

        // Trung tâm điều hành (CMS)
        Route::get('/', \App\Livewire\Admin\OperationsCenter::class)->name('operations');

        // Nhật ký hoạt động (audit log)
        Route::get('nhat-ky-hoat-dong', \App\Livewire\Admin\AuditLog::class)->name('audit-log');

        // Ổ tài liệu (document drive)
        Route::get('tai-lieu', \App\Livewire\Admin\DocumentDrive::class)->name('drive');
        Route::get('tai-lieu/file/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'file'])->name('drive.file');

        // Mục tài liệu (CRUD danh mục)
        Route::resource('document-categories', DocumentCategoryController::class);

        // Biểu mẫu (danh sách có lọc theo TL / trạng thái + tìm kiếm)
        Route::get('form-templates', \App\Livewire\Admin\FormTemplateList::class)
            ->name('form-templates.index');
        Route::get('form-templates/create', [FormTemplateController::class, 'create'])
            ->name('form-templates.create');
        Route::get('form-templates/{template}/review', [FormTemplateController::class, 'review'])
            ->name('form-templates.review');
        Route::get('form-templates/{template}/edit', [FormTemplateController::class, 'edit'])
            ->name('form-templates.edit');
        Route::put('form-templates/{template}', [FormTemplateController::class, 'update'])
            ->name('form-templates.update');
        Route::delete('form-templates/{template}', [FormTemplateController::class, 'destroy'])
            ->name('form-templates.destroy');
    });
});
