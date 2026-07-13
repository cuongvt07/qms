<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
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
