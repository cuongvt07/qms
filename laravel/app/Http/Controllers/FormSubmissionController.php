<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Models\FormTemplateVersion;
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

    /** Xuất .docx cho bản điền trực tiếp (raw vals: text + chk_ ☒/☐). */
    public function inlineExport(FormSubmission $submission, HtmlFormService $html): BinaryFileResponse
    {
        if ($submission->user_id !== auth()->id() && ! (auth()->user()->is_admin ?? false)) {
            abort(403);
        }
        $version  = $submission->templateVersion;
        $template = $version->formTemplate;
        $tmpPath  = $html->fill($template, $submission->data_json ?? [], $version->fields);
        $filename = sprintf('%s_%s.docx', $template->ma_bm, $submission->ngay_nhap->format('Ymd'));

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
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

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }
}
