<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Models\FormSubmissionAttachment;
use App\Models\FormTemplateVersion;
use App\Services\ActivityLogger;
use App\Services\DocxExportService;
use App\Services\HtmlFormService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FormSubmissionController extends Controller
{
    /** Trả file .docx gốc của 1 version (để docx-preview render phía trình duyệt). */
    public function sourceDocx(int $versionId): BinaryFileResponse
    {
        $version  = FormTemplateVersion::findOrFail($versionId);
        $template = $version->formTemplate;
        $path     = Storage::disk('local')->path($template->file_goc_path);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'inline; filename="' . $template->ma_bm . '.docx"',
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
        ]);
    }

    /** Xuất .docx cho bản điền trực tiếp — DÙNG CHUNG DocxExportService với dạng phiếu (đồng bộ). */
    public function inlineExport(FormSubmission $submission, DocxExportService $exportService): BinaryFileResponse
    {
        if ($submission->user_id !== auth()->id() && ! (auth()->user()->is_admin ?? false)) {
            abort(403);
        }
        $template = $submission->templateVersion->formTemplate;
        $tmpPath  = $exportService->export($submission);
        $filename = sprintf('%s_%s.docx', $template->ma_bm, $submission->ngay_nhap->format('Ymd'));

        ActivityLogger::log('export', "Tải .docx biểu mẫu {$template->ma_bm} — ngày " . $submission->ngay_nhap->format('d/m/Y'), $submission);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

    /** Phục vụ file đính kèm (ảnh xem trực tiếp, file khác tải về). */
    public function attachment(FormSubmissionAttachment $attachment): BinaryFileResponse
    {
        $sub = $attachment->submission;
        if ($sub->user_id !== auth()->id() && ! (auth()->user()->is_admin ?? false)) {
            abort(403);
        }
        $path = Storage::disk('local')->path($attachment->path);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type'        => $attachment->mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($attachment->original_name) . '"',
        ]);
    }

    public function export(FormSubmission $submission, DocxExportService $exportService): BinaryFileResponse
    {
        // Chỉ cho phép chủ sở hữu hoặc admin tải xuống
        if ($submission->user_id !== auth()->id() && ! (auth()->user()->is_admin ?? false)) {
            abort(403);
        }

        $tmpPath = $exportService->export($submission);

        $filename = sprintf(
            '%s_%s_%s.docx',
            $submission->templateVersion->formTemplate->ma_bm,
            $submission->user->name,
            $submission->ngay_nhap->format('Ymd')
        );

        ActivityLogger::log('export', 'Tải .docx biểu mẫu ' . $submission->templateVersion->formTemplate->ma_bm . ' — ngày ' . $submission->ngay_nhap->format('d/m/Y'), $submission);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }
}
