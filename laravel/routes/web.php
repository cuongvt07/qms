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

Route::get('/', fn () => redirect()->route('env.page'));

// Auth (Laravel Breeze/Fortify sẽ đăng ký các route login/logout)
require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {

    // ===== Module QMS dạng CRM (chuyển từ mẫu thiết kế) =====
    Route::get('/qms/moi-truong', [\App\Http\Controllers\EnvMonitorController::class, 'page'])->name('env.page');
    Route::get('/qms/moi-truong/du-lieu', [\App\Http\Controllers\EnvMonitorController::class, 'state'])->name('env.state');
    Route::post('/qms/moi-truong/du-lieu', [\App\Http\Controllers\EnvMonitorController::class, 'save'])->name('env.save');

    Route::get('/qms/trang-thiet-bi', [\App\Http\Controllers\DeviceEventController::class, 'page'])->name('dev.page');
    Route::get('/qms/trang-thiet-bi/du-lieu', [\App\Http\Controllers\DeviceEventController::class, 'state'])->name('dev.state');
    Route::post('/qms/trang-thiet-bi/du-lieu', [\App\Http\Controllers\DeviceEventController::class, 'save'])->name('dev.save');

    Route::get('/qms/rac-thai', [\App\Http\Controllers\WasteLogController::class, 'page'])->name('waste.page');
    Route::get('/qms/rac-thai/du-lieu', [\App\Http\Controllers\WasteLogController::class, 'state'])->name('waste.state');
    Route::post('/qms/rac-thai/du-lieu', [\App\Http\Controllers\WasteLogController::class, 'save'])->name('waste.save');

    Route::get('/qms/cau-hinh', [\App\Http\Controllers\QmsConfigController::class, 'page'])->name('config.page');
    Route::get('/qms/cau-hinh/du-lieu', [\App\Http\Controllers\QmsConfigController::class, 'state'])->name('config.state');
    Route::post('/qms/cau-hinh/du-lieu', [\App\Http\Controllers\QmsConfigController::class, 'save'])->name('config.save');

    Route::get('/qms/su-dung-thiet-bi', [\App\Http\Controllers\DeviceUsageController::class, 'page'])->name('usage.page');

    Route::get('/qms/nhap-xuat', [\App\Http\Controllers\StockEntryController::class, 'page'])->name('entry.page');
    Route::get('/qms/nhap-xuat/du-lieu', [\App\Http\Controllers\StockEntryController::class, 'state'])->name('entry.state');
    Route::post('/qms/nhap-xuat/luu', [\App\Http\Controllers\StockEntryController::class, 'store'])->name('entry.save');
    Route::post('/qms/nhap-xuat/tao-nhanh', [\App\Http\Controllers\StockEntryController::class, 'quickCreate'])->name('entry.quick');
    Route::post('/qms/ma-hang/{product}/anh', [\App\Http\Controllers\StockEntryController::class, 'setImage'])->name('item.image.set');
    Route::get('/qms/ma-hang/{product}/anh', [\App\Http\Controllers\StockEntryController::class, 'image'])->name('item.image');
    Route::get('/qms/kho', [\App\Http\Controllers\StockItemController::class, 'dashboardPage'])->name('stock.dash');
    Route::get('/qms/kho/so-lieu', [\App\Http\Controllers\StockItemController::class, 'dashboard'])->name('stock.dash.data');
    Route::get('/qms/ma-hang', [\App\Http\Controllers\StockItemController::class, 'page'])->name('item.page');
    Route::get('/qms/ma-hang/du-lieu', [\App\Http\Controllers\StockItemController::class, 'state'])->name('item.state');
    Route::post('/qms/ma-hang/du-lieu', [\App\Http\Controllers\StockItemController::class, 'save'])->name('item.save');
    Route::get('/qms/the-kho', [\App\Http\Controllers\StockCardController::class, 'page'])->name('stock.page');
    Route::get('/qms/the-kho/du-lieu', [\App\Http\Controllers\StockCardController::class, 'state'])->name('stock.state');
    Route::post('/qms/the-kho/du-lieu', [\App\Http\Controllers\StockCardController::class, 'save'])->name('stock.save');
    // Sổ soạn tiêu bản + hóa mô miễn dịch
    Route::get('/qms/so-tieu-ban', [\App\Http\Controllers\SlideBookController::class, 'page'])->name('slide.page');
    Route::get('/qms/so-tieu-ban/du-lieu', [\App\Http\Controllers\SlideBookController::class, 'state'])->name('slide.state');
    Route::post('/qms/so-tieu-ban/luu', [\App\Http\Controllers\SlideBookController::class, 'saveRows'])->name('slide.save');
    Route::get('/qms/so-tieu-ban/cho-xu-ly', [\App\Http\Controllers\SlideBookController::class, 'pendingState'])->name('slide.pending');
    Route::post('/qms/so-tieu-ban/cho-xu-ly', [\App\Http\Controllers\SlideBookController::class, 'pendingSave'])->name('slide.pending.save');
    Route::get('/qms/so-tieu-ban/phien/ma-trong', [\App\Http\Controllers\SlideBookController::class, 'sessionCandidates'])->name('slide.session.ma');
    Route::post('/qms/so-tieu-ban/phien', [\App\Http\Controllers\SlideBookController::class, 'saveSession'])->name('slide.session.save');
    Route::get('/qms/so-tieu-ban/doc', [\App\Http\Controllers\SlideBookController::class, 'readerState'])->name('slide.reader');
    Route::post('/qms/so-tieu-ban/danh-dau-doc', [\App\Http\Controllers\SlideBookController::class, 'markRead'])->name('slide.mark');
    Route::post('/qms/so-tieu-ban/doc/nhan-gia', [\App\Http\Controllers\SlideBookController::class, 'takeRacks'])->name('slide.take');
    Route::post('/qms/so-tieu-ban/hoan-tat', [\App\Http\Controllers\SlideBookController::class, 'finishSlides'])->name('slide.finish');
    Route::get('/qms/so-tieu-ban/lich-su', [\App\Http\Controllers\SlideBookController::class, 'historyState'])->name('slide.history');
    Route::get('/qms/so-tieu-ban/lich-su/xuat-excel', [\App\Http\Controllers\SlideBookController::class, 'exportHistory'])->name('slide.history.export');
    Route::get('/qms/so-tieu-ban/tinh-trang', [\App\Http\Controllers\SlideBookController::class, 'statusState'])->name('slide.status');
    Route::get('/qms/so-tieu-ban/tien-trinh', [\App\Http\Controllers\SlideBookController::class, 'trace'])->name('slide.trace');
    Route::get('/qms/so-tieu-ban/hmmd', [\App\Http\Controllers\SlideBookController::class, 'ihcState'])->name('slide.ihc');
    Route::post('/qms/so-tieu-ban/hmmd', [\App\Http\Controllers\SlideBookController::class, 'ihcSave'])->name('slide.ihc.save');
    Route::post('/qms/so-tieu-ban/hmmd/{ihc}/buoc', [\App\Http\Controllers\SlideBookController::class, 'ihcStep'])->name('slide.ihc.step');
    Route::get('/qms/so-tieu-ban/hoi-chan', [\App\Http\Controllers\SlideBookController::class, 'consultState'])->name('slide.consult');
    Route::post('/qms/so-tieu-ban/hoi-chan/mo', [\App\Http\Controllers\SlideBookController::class, 'consultOpen'])->name('slide.consult.open');
    Route::post('/qms/so-tieu-ban/hoi-chan/y-kien', [\App\Http\Controllers\SlideBookController::class, 'consultNote'])->name('slide.consult.note');
    Route::post('/qms/so-tieu-ban/hoi-chan/anh', [\App\Http\Controllers\SlideBookController::class, 'consultUpload'])->name('slide.consult.upload');
    Route::get('/qms/so-tieu-ban/hoi-chan/anh/{image}', [\App\Http\Controllers\SlideBookController::class, 'consultImage'])->name('slide.consult.image');
    Route::delete('/qms/so-tieu-ban/hoi-chan/anh/{image}', [\App\Http\Controllers\SlideBookController::class, 'consultImageDelete'])->name('slide.consult.image.del');
    Route::post('/qms/so-tieu-ban/hoi-chan/chot', [\App\Http\Controllers\SlideBookController::class, 'consultClose'])->name('slide.consult.close');
    Route::get('/qms/so-tieu-ban/benh-nhan', [\App\Http\Controllers\SlideBookController::class, 'patientLookup'])->name('slide.patient');
    Route::get('/qms/so-tieu-ban/xuat-excel', [\App\Http\Controllers\SlideBookController::class, 'exportBook'])->name('slide.export');
    Route::get('/qms/so-tieu-ban/hmmd/xuat-excel', [\App\Http\Controllers\SlideBookController::class, 'exportIhc'])->name('slide.ihc.export');

    // Xuất Excel cho module kho
    Route::get('/qms/the-kho/xuat-excel', [\App\Http\Controllers\StockCardController::class, 'exportCard'])->name('stock.export');
    Route::get('/qms/ma-hang/xuat-excel', [\App\Http\Controllers\StockItemController::class, 'exportItems'])->name('item.export');

    Route::get('/qms/su-dung-thiet-bi/du-lieu', [\App\Http\Controllers\DeviceUsageController::class, 'state'])->name('usage.state');
    Route::post('/qms/su-dung-thiet-bi/du-lieu', [\App\Http\Controllers\DeviceUsageController::class, 'save'])->name('usage.save');

    // Mẫu mặc định cho các form nhập nhiều
    Route::get('/qms/mac-dinh/{module}', [\App\Http\Controllers\QmsPresetController::class, 'index'])->name('preset.index');
    Route::post('/qms/mac-dinh/{module}', [\App\Http\Controllers\QmsPresetController::class, 'store'])->name('preset.store');
    Route::delete('/qms/mac-dinh/{module}', [\App\Http\Controllers\QmsPresetController::class, 'destroy'])->name('preset.destroy');

    // Luồng nhập liệu nối tiếp
    Route::get('/qms/luong', [\App\Http\Controllers\QmsFlowController::class, 'state'])->name('flow.state');
    Route::get('/qms/nhap-lieu', [\App\Http\Controllers\QmsFlowController::class, 'page'])->name('flow.page');
    Route::get('/qms/vao-luong', [\App\Http\Controllers\QmsFlowController::class, 'entry'])->name('flow.entry');

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
        Route::get('tai-lieu/pdf/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'pdf'])->name('drive.pdf');
        Route::post('tai-lieu/chunk', [\App\Http\Controllers\Admin\DocumentController::class, 'chunk'])->name('drive.chunk');
        Route::post('tai-lieu/finalize', [\App\Http\Controllers\Admin\DocumentController::class, 'chunkFinalize'])->name('drive.finalize');

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
