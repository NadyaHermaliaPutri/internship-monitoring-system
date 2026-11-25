<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskReviewDecision;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Task|null $task */
        $task = $this->route('task');

        return $task && ($this->user()?->can('review', $task) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'task_submission_id' => ['nullable', 'integer'],
            'decision' => [
                'required',
                'string',
                Rule::in([
                    TaskReviewDecision::Accepted->value,
                    TaskReviewDecision::RevisionRequested->value,
                ]),
            ],
            'feedback' => [
                'nullable',
                'required_if:decision,'.TaskReviewDecision::RevisionRequested->value,
                'string',
                'min:5',
                'max:3000',
            ],
            // Hardening anti-tampering: status hanya ditentukan oleh decision di backend
            'status' => ['prohibited'],
            'accepted_at' => ['prohibited'],
            'accepted_by' => ['prohibited'],
            'pic_user_id' => ['prohibited'],
            'intern_user_id' => ['prohibited'],
            'assigned_at' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan peninjauan tugas wajib dipilih.',
            'decision.in' => 'Keputusan peninjauan hanya boleh Diterima (Accepted) atau Perlu Revisi (Revision Requested).',
            'feedback.required_if' => 'Catatan revisi (feedback) wajib diisi ketika meminta revisi kepada peserta.',
            'feedback.min' => 'Catatan / feedback minimal 5 karakter agar jelas bagi peserta.',
            'feedback.max' => 'Catatan / feedback maksimal 3000 karakter.',
        ];
    }
}
