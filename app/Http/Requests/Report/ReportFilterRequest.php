<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Hanya Admin / HR yang berwenang mengakses laporan.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report_type' => [
                'required',
                'string',
                Rule::in(['attendance', 'leave', 'logbook', 'task', 'monitoring']),
            ],
            'intern_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'intern'),
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
        ];
    }

    /**
     * Pesan kesalahan kustom dalam bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'report_type.required' => 'Jenis laporan wajib dipilih.',
            'report_type.in' => 'Jenis laporan tidak valid. Pilih salah satu: Presensi, Izin/Sakit, Logbook, Tugas, atau Monitoring.',
            'intern_id.exists' => 'Peserta magang yang dipilih tidak valid atau bukan peserta magang.',
            'start_date.required' => 'Tanggal awal periode wajib diisi.',
            'start_date.date' => 'Format tanggal awal tidak valid.',
            'end_date.required' => 'Tanggal akhir periode wajib diisi.',
            'end_date.date' => 'Format tanggal akhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal akhir periode harus sama atau setelah tanggal awal.',
        ];
    }
}
