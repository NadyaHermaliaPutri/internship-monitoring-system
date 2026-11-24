<?php

namespace App\Http\Requests\Logbook;

use App\Enums\LogbookStatus;
use App\Models\Logbook;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Logbook|null $logbook */
        $logbook = $this->route('logbook');

        return $logbook && ($this->user()?->can('review', $logbook) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => [
                'required',
                'string',
                Rule::in([
                    LogbookStatus::Approved->value,
                    LogbookStatus::RevisionRequested->value,
                ]),
            ],
            'review_note' => [
                'nullable',
                'required_if:decision,'.LogbookStatus::RevisionRequested->value,
                'string',
                'min:5',
                'max:2000',
            ],
            // Hardening anti-tampering: cegah manipulasi kolom internal logbook lewat payload review
            'status' => ['prohibited'],
            'source' => ['prohibited'],
            'intern_user_id' => ['prohibited'],
            'reviewed_by' => ['prohibited'],
            'reviewed_at' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan peninjauan logbook wajib dipilih.',
            'decision.in' => 'Keputusan peninjauan hanya boleh Disetujui (Approved) atau Perlu Revisi (Revision Requested).',
            'review_note.required_if' => 'Catatan revisi wajib diisi ketika meminta revisi kepada peserta.',
            'review_note.min' => 'Catatan revisi minimal 5 karakter agar jelas bagi peserta.',
            'review_note.max' => 'Catatan review maksimal 2000 karakter.',
        ];
    }
}
