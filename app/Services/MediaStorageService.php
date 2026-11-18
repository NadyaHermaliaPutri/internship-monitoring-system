<?php

namespace App\Services;

use App\Models\MediaFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStorageService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('wantrace.uploads.disk', 'local_private');
    }

    /**
     * Menyimpan file unggahan (UploadedFile) ke private disk dan mencatat metadata di media_files.
     */
    public function storeUploadedFile(
        UploadedFile $file,
        User $owner,
        string $category
    ): MediaFile {
        $this->validateCategory($category);

        $sanitizedOriginalName = $this->sanitizeFileName($file->getClientOriginalName());
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $directory = config("wantrace.uploads.{$category}.directory", 'uploads');

        // Nama file acak dan aman (anti path traversal & collision)
        $uniqueFileName = sprintf('%s_%s.%s', date('Ymd_His'), Str::random(24), strtolower($extension));
        $relativePath = $directory . '/' . $uniqueFileName;

        // Simpan file fisik ke storage/app/private via disk local_private
        Storage::disk($this->disk)->putFileAs($directory, $file, $uniqueFileName);

        return MediaFile::create([
            'owner_user_id' => $owner->id,
            'category' => $category,
            'disk' => $this->disk,
            'file_path' => $relativePath,
            'original_name' => $sanitizedOriginalName,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size_bytes' => $file->getSize() ?: 0,
            'uploaded_at' => Carbon::now(config('wantrace.timezone', 'Asia/Jakarta')),
        ]);
    }

    /**
     * Menyimpan foto bukti selfie presensi dari Base64 string webcam.
     */
    public function storeBase64Image(
        string $base64Data,
        User $owner,
        string $category = 'attendance_photo'
    ): MediaFile {
        $this->validateCategory($category);

        // Parsing data URI scheme (e.g. data:image/jpeg;base64,xxxx)
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $extension = strtolower($type[1]);
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }
        } else {
            $data = $base64Data;
            $extension = 'jpg';
        }

        $decodedBinary = base64_decode($data);
        if ($decodedBinary === false) {
            throw new InvalidArgumentException('Format gambar base64 tidak valid.');
        }

        $sizeBytes = strlen($decodedBinary);
        $maxSizeBytes = config('wantrace.uploads.attendance_photo.max_size_kb', 2048) * 1024;

        if ($sizeBytes > $maxSizeBytes) {
            throw new InvalidArgumentException('Ukuran foto selfie melebihi batas maksimal (2 MB).');
        }

        $directory = config("wantrace.uploads.{$category}.directory", 'presensi');
        $uniqueFileName = sprintf('%s_selfie_%s.%s', date('Ymd_His'), Str::random(24), $extension);
        $relativePath = $directory . '/' . $uniqueFileName;

        Storage::disk($this->disk)->put($relativePath, $decodedBinary);

        $mimeType = match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return MediaFile::create([
            'owner_user_id' => $owner->id,
            'category' => $category,
            'disk' => $this->disk,
            'file_path' => $relativePath,
            'original_name' => "presensi_selfie_{$owner->id}_" . date('Ymd_His') . ".{$extension}",
            'mime_type' => $mimeType,
            'file_size_bytes' => $sizeBytes,
            'uploaded_at' => Carbon::now(config('wantrace.timezone', 'Asia/Jakarta')),
        ]);
    }

    /**
     * Menghasilkan StreamedResponse untuk menampilkan atau mengunduh file privat.
     */
    public function responseStream(MediaFile $mediaFile, bool $asDownload = false): StreamedResponse
    {
        $disk = Storage::disk($mediaFile->disk ?: $this->disk);

        if (! $disk->exists($mediaFile->file_path)) {
            abort(404, 'Berkas fisik tidak ditemukan di penyimpanan server.');
        }

        $headers = [
            'Content-Type' => $mediaFile->mime_type,
            'Content-Length' => $mediaFile->file_size_bytes,
        ];

        if ($asDownload) {
            $disposition = 'attachment; filename="' . addslashes($mediaFile->original_name) . '"';
        } else {
            $disposition = 'inline; filename="' . addslashes($mediaFile->original_name) . '"';
        }

        $headers['Content-Disposition'] = $disposition;

        return response()->stream(function () use ($disk, $mediaFile) {
            $stream = $disk->readStream($mediaFile->file_path);
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, $headers);
    }

    /**
     * Menghapus file fisik dan metadata jika diperlukan.
     */
    public function deleteFile(MediaFile $mediaFile): bool
    {
        $disk = Storage::disk($mediaFile->disk ?: $this->disk);

        if ($disk->exists($mediaFile->file_path)) {
            $disk->delete($mediaFile->file_path);
        }

        return $mediaFile->delete();
    }

    protected function validateCategory(string $category): void
    {
        $allowed = ['attendance_photo', 'leave_evidence', 'task_evidence'];
        if (! in_array($category, $allowed, true)) {
            throw new InvalidArgumentException("Kategori file '{$category}' tidak dikenali oleh sistem.");
        }
    }

    protected function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[^\w\s\.-]/', '_', $name);
        return Str::limit($name, 250, '');
    }
}
