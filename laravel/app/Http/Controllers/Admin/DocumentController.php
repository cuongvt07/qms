<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    /** Nhận 1 mảnh (chunk) — ghi theo index để an toàn khi retry. */
    public function chunk(Request $request): JsonResponse
    {
        $request->validate([
            'upload_id' => 'required|string|max:80',
            'index'     => 'required|integer|min:0|max:100000',
            'total'     => 'required|integer|min:1|max:100000',
            'chunk'     => 'required|file|max:12288',   // 12MB/mảnh
        ]);
        $id  = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->input('upload_id'));
        $dir = storage_path('app/chunks/' . $id);
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $request->file('chunk')->move($dir, ((int) $request->input('index')) . '.part');   // ghi đè nếu retry
        return response()->json(['ok' => true]);
    }

    /** Ghép các mảnh thành file, tạo record Document trong thư mục đích. */
    public function chunkFinalize(Request $request): JsonResponse
    {
        $data = $request->validate([
            'upload_id'   => 'required|string|max:80',
            'total'       => 'required|integer|min:1|max:100000',
            'name'        => 'required|string|max:255',
            'category_id' => 'required|integer|exists:document_categories,id',
            'folder_id'   => 'nullable|integer',
            'mime'        => 'nullable|string|max:150',
        ]);
        $id    = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['upload_id']);
        $dir   = storage_path('app/chunks/' . $id);
        $total = (int) $data['total'];
        abort_unless(is_dir($dir), 404, 'Không thấy dữ liệu tải lên.');

        // Thư mục đích phải thuộc đúng ổ (bảo mật)
        $folderId = $data['folder_id'] ?? null;
        if ($folderId) {
            $ok = Document::where('id', $folderId)->where('type', 'folder')
                ->where('document_category_id', $data['category_id'])->exists();
            if (! $ok) {
                $folderId = null;
            }
        }

        $catId = (int) $data['category_id'];
        $ext   = pathinfo($data['name'], PATHINFO_EXTENSION);
        $rel   = 'documents/' . $catId . '/' . Str::random(40) . ($ext ? '.' . strtolower($ext) : '');
        Storage::disk('local')->makeDirectory('documents/' . $catId);
        $destAbs = Storage::disk('local')->path($rel);

        $out = fopen($destAbs, 'wb');
        for ($i = 0; $i < $total; $i++) {
            $pf = $dir . '/' . $i . '.part';
            if (! is_file($pf)) {
                fclose($out);
                @unlink($destAbs);
                return response()->json(['ok' => false, 'error' => 'Thiếu mảnh ' . $i], 422);
            }
            $in = fopen($pf, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }
        fclose($out);

        // dọn thư mục mảnh
        foreach (glob($dir . '/*') ?: [] as $g) {
            @unlink($g);
        }
        @rmdir($dir);

        $doc = Document::create([
            'document_category_id' => $catId,
            'parent_id'            => $folderId,
            'type'                 => 'file',
            'name'                 => $data['name'],
            'path'                 => $rel,
            'mime'                 => $data['mime'] ?? null,
            'size'                 => filesize($destAbs) ?: 0,
            'uploaded_by'          => auth()->id(),
            'source'               => 'upload',
        ]);
        ActivityLogger::log('document', 'Tải lên tệp "' . $data['name'] . '" vào ổ ' . (DocumentCategory::find($catId)?->ten_muc ?? ''));

        return response()->json(['ok' => true, 'id' => $doc->id]);
    }

    /** Chuyển file office (.doc/.docx/.xls/.xlsx/.ppt/.pptx) sang PDF để xem online. Cache theo mtime. */
    public function pdf(Document $document): BinaryFileResponse
    {
        abort_if($document->type !== 'file' || ! $document->path, 404);
        $src = Storage::disk('local')->path($document->path);
        abort_unless(is_file($src), 404);

        $dir = storage_path('app/pdf_cache');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $cache = $dir . '/' . $document->id . '_' . filemtime($src) . '.pdf';

        if (! is_file($cache)) {
            $profile = $dir . '/.loprofile';   // soffice cần thư mục profile ghi được (chạy dưới www-data)
            $res = \Illuminate\Support\Facades\Process::timeout(120)
                ->env(['HOME' => $dir])
                ->run([
                    'soffice', '--headless', '-env:UserInstallation=file://' . $profile,
                    '--convert-to', 'pdf', '--outdir', $dir, $src,
                ]);
            $made = $dir . '/' . pathinfo($src, PATHINFO_FILENAME) . '.pdf';
            if (is_file($made)) {
                @rename($made, $cache);
            }
            abort_unless(is_file($cache), 422, 'Không chuyển được sang PDF: ' . substr(trim($res->errorOutput() . ' ' . $res->output()), 0, 300));
        }

        return response()->file($cache, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes(pathinfo($document->name, PATHINFO_FILENAME)) . '.pdf"',
        ]);
    }

    /** Xem/tải file trong ổ tài liệu. ?dl=1 để tải xuống. */
    public function file(Request $request, Document $document): BinaryFileResponse
    {
        abort_if($document->type !== 'file' || ! $document->path, 404);
        $abs = Storage::disk('local')->path($document->path);
        abort_unless(is_file($abs), 404);

        if ($request->boolean('dl')) {
            return response()->download($abs, $document->name);
        }

        return response()->file($abs, [
            'Content-Type'        => $document->mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($document->name) . '"',
        ]);
    }
}
