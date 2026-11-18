<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    /**
     * Otorisasi form request (hanya Peserta Magang yang dapat mengajukan).
     */
    public function authorize(): bool
    {
        return $this->user()?->isIntern() === true;
    }

    /**
     * Aturan validasi pengajuan izin/sakit.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxSizeKb = config('wantrace.uploads.leave_evidence.max_size_kb', 5120);
        $mimes = implode(',', config('wantrace.uploads.leave_evidence.mimes', ['jpg', 'jpeg', 'png', 'pdf']));

        return [
            'leave_type' => ['required', 'in:leave,sick'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'evidence' => [
                'required',
                'file',
                "mimes:{$mimes}",
                "max:{$maxSizeKb}",
            ],
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
            'leave_type.required' => 'Jenis pengajuan wajib dipilih (Izin atau Sakit).',
            'leave_type.in' => 'Jenis pengajuan harus bernilai izin (leave) atau sakit (sick).',
            'start_date.required' => 'Tanggal mulai izin wajib diisi.',
            'start_date.date_format' => 'Format tanggal mulai harus YYYY-MM-DD.',
            'end_date.required' => 'Tanggal selesai izin wajib diisi.',
            'end_date.date_format' => 'Format tanggal selesai harus YYYY-MM-DD.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'reason.required' => 'Alasan permohonan izin/sakit wajib diisi.',
            'reason.min' => 'Alasan permohonan minimal 5 karakter.',
            'reason.max' => 'Alasan permohonan maksimal 1000 karakter.',
            'evidence.required' => 'Berkas bukti (surat dokter / surat pengantar) wajib dilampirkan.',
            'evidence.file' => 'Berkas bukti tidak terunggah dengan sempurna.',
            'evidence.mimes' => 'Format berkas bukti harus berupa JPG, JPEG, PNG, atau PDF.',
            'evidence.max' => 'Ukuran berkas bukti maksimal 5 MB.',
        ];
    }
}
