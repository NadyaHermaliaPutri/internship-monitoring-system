<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class RejectAttendanceRequest extends FormRequest
{
    /**
     * Otorisasi form request (hanya Admin/HR).
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Aturan validasi penolakan presensi.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notes' => ['required', 'string', 'min:5', 'max:1000'],
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
            'notes.required' => 'Alasan penolakan presensi wajib diisi.',
            'notes.min' => 'Alasan penolakan presensi minimal 5 karakter.',
            'notes.max' => 'Alasan penolakan presensi maksimal 1000 karakter.',
        ];
    }
}
