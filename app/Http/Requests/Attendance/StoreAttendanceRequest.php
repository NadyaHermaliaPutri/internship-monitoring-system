<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Otorisasi form request (hanya Peserta Magang yang dapat check-in).
     */
    public function authorize(): bool
    {
        return $this->user()?->isIntern() === true;
    }

    /**
     * Aturan validasi unggah foto bukti selfie presensi masuk.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxSizeKb = config('wantrace.uploads.attendance_photo.max_size_kb', 2048);
        $mimes = implode(',', config('wantrace.uploads.attendance_photo.mimes', ['jpeg', 'jpg', 'png', 'webp']));

        return [
            'photo' => [
                'required',
                'file',
                'image',
                "mimes:{$mimes}",
                "max:{$maxSizeKb}",
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan kesalahan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.required' => 'Foto bukti selfie presensi wajib diambil menggunakan kamera.',
            'photo.file' => 'Berkas foto tidak terunggah dengan sempurna.',
            'photo.image' => 'Berkas yang diunggah harus berupa citra gambar valid.',
            'photo.mimes' => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran file foto selfie maksimal 2 MB.',
            'notes.max' => 'Catatan presensi tidak boleh lebih dari 500 karakter.',
        ];
    }
}
