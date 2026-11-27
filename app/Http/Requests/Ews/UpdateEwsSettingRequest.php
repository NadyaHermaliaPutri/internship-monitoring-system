<?php

namespace App\Http\Requests\Ews;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEwsSettingRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk melakukan permintaan ini.
     * Hanya Administrator / HR yang diizinkan memperbarui jam cutoff.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     * Format waktu wajib HH:MM (contoh: 09:00 atau 17:00).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendance_cutoff_time' => ['required', 'date_format:H:i'],
            'logbook_cutoff_time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * Pesan kesalahan khusus untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attendance_cutoff_time.required' => 'Jam cutoff EWS presensi wajib diisi.',
            'attendance_cutoff_time.date_format' => 'Format jam cutoff presensi harus HH:MM (contoh: 09:00).',
            'logbook_cutoff_time.required' => 'Jam cutoff EWS logbook wajib diisi.',
            'logbook_cutoff_time.date_format' => 'Format jam cutoff logbook harus HH:MM (contoh: 17:00).',
        ];
    }
}
