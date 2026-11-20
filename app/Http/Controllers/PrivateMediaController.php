<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateMediaController extends Controller
{
    public function __construct(
        protected MediaStorageService $mediaStorageService
    ) {}

    /**
     * Menyajikan berkas privat dari disk local_private secara aman.
     * Tidak pernah membuat tautan/URL publik di filesystem.
     * Otorisasi ketat diperiksa via MediaFilePolicy.
     */
    public function show(Request $request, MediaFile $mediaFile): StreamedResponse
    {
        // Periksa otorisasi (Admin, Pemilik File, atau PIC yang berhak)
        Gate::authorize('view', $mediaFile);

        $asDownload = (bool) $request->boolean('download', false);

        return $this->mediaStorageService->responseStream($mediaFile, $asDownload);
    }
}
