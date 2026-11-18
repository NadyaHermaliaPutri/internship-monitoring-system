<?php

namespace App\Http\Requests\Calendar;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarExceptionRequest extends FormRequest
{
    /**
     * Otorisasi form request (hanya Admin/HR).
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Aturan validasi server-side pengecualian kalender.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exception_date' => ['required', 'date_format:Y-m-d'],
            'type' => ['required', 'in:holiday,working_day'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
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
            'exception_date.required' => 'Tanggal pengecualian wajib dipilih.',
            'exception_date.date_format' => 'Format tanggal harus sesuai standar YYYY-MM-DD.',
            'type.required' => 'Tipe pengecualian kalender wajib ditentukan.',
            'type.in' => 'Tipe harus bernilai hari libur (holiday) atau hari kerja (working_day).',
            'name.required' => 'Nama / alasan hari libur atau hari kerja khusus wajib diisi.',
            'name.max' => 'Nama alasan tidak boleh melebihi 150 karakter.',
            'description.max' => 'Keterangan tambahan tidak boleh melebihi 1000 karakter.',
        ];
    }
}
