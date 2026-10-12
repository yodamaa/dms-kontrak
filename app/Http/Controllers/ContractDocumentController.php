<?php

namespace App\Http\Controllers;

use App\Models\ContractDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractDocumentController extends Controller
{
    /**
     * Download contract document.
     */
    public function download(ContractDocument $document)
    {
        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File dokumen tidak ditemukan di server.');
        }

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    /**
     * Preview contract document inline in browser.
     */
    public function preview(ContractDocument $document)
    {
        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File dokumen tidak ditemukan di server.');
        }

        $path = Storage::disk('local')->path($document->file_path);

        return response()->file($path, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="' . addslashes($document->original_name) . '"',
        ]);
    }
}
