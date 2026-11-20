<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInternProfileRequest extends FormRequest
{
    /**
     * Pastikan hanya Admin/HR yang dapat mengelola profil peserta magang.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    /**
     * Aturan validasi profil peserta magang sesuai spesifikasi Database Dictionary PT WAN.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $targetUser */
        $targetUser = $this->route('user');
        $existingProfileId = $targetUser instanceof User ? $targetUser->internProfile?->id : null;

        return [
            'institution' => [
                'nullable',
                'string',
                'max:150',
            ],
            'study_program' => [
                'nullable',
                'string',
                'max:150',
            ],
            'internship_start_date' => [
                'required',
                'date',
            ],
            'internship_end_date' => [
                'nullable',
                'date',
                'after_or_equal:internship_start_date',
            ],
        ];
    }

    /**
     * Pesan error kustom dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'intern_code.max' => 'Kode peserta maksimal 50 karakter.',
            'intern_code.unique' => 'Kode peserta ini sudah terdaftar untuk peserta lain.',
            'institution.max' => 'Nama institusi/kampus maksimal 150 karakter.',
            'study_program.max' => 'Nama program studi maksimal 150 karakter.',
            'internship_start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'internship_start_date.date' => 'Format tanggal mulai magang tidak valid.',
            'internship_end_date.date' => 'Format tanggal selesai magang tidak valid.',
            'internship_end_date.after_or_equal' => 'Tanggal selesai magang harus sama dengan atau setelah tanggal mulai magang.',
        ];
    }
}
