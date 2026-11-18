<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Otorisasi form request (hanya Admin/HR yang dapat mengoreksi presensi).
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Aturan validasi koreksi status presensi oleh Admin/HR.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:on_time,late'],
            'notes' => ['required', 'string', 'max:500'],
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
            'status.required' => 'Status presensi wajib dipilih.',
            'status.in' => 'Status presensi hanya boleh bernilai tepat waktu (on_time) atau terlambat (late).',
            'notes.required' => 'Catatan / alasan koreksi presensi wajib disertakan.',
            'notes.max' => 'Catatan koreksi maksimal 500 karakter.',
        ];
    }
}
