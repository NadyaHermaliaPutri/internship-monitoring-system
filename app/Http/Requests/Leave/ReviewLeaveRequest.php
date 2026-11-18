<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class ReviewLeaveRequest extends FormRequest
{
    /**
     * Otorisasi form request (hanya Admin/HR yang dapat menyetujui/menolak).
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Aturan validasi keputusan verifikasi izin/sakit.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected'],
            'review_notes' => ['required_if:decision,rejected', 'nullable', 'string', 'max:1000'],
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
            'decision.required' => 'Keputusan verifikasi izin wajib ditentukan.',
            'decision.in' => 'Keputusan hanya boleh bernilai disetujui (approved) atau ditolak (rejected).',
            'review_notes.required_if' => 'Catatan keputusan wajib diisi apabila permohonan izin ditolak.',
            'review_notes.max' => 'Catatan keputusan maksimal 1000 karakter.',
        ];
    }
}
