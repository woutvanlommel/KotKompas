<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentDownloadController extends Controller
{
    public function __invoke(Request $request, Document $document): BinaryFileResponse
    {
        abort_unless($request->user()->can('view', $document), 403);

        $media = $document->getFirstMedia('document');
        abort_unless($media !== null, 404);

        $conversion = $request->query('conversion');

        if ($conversion === 'thumbnail' && $media->hasGeneratedConversion('thumbnail')) {
            return response()->file($media->getPath('thumbnail'));
        }

        return response()->file($media->getPath());
    }
}
