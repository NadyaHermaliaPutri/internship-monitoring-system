<?php

namespace App\Http\Requests\Logbook;

use App\Enums\LogbookActivityStatus;
use App\Models\Logbook;
use App\Services\LogbookService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Logbook|null $logbook */
        $logbook = $this->route('logbook');

        return $logbook && ($this->user()?->can('update', $logbook) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(LogbookService $logbookService): array
    {
        $earliest = $logbookService->earliestAllowedDateString();
        $today = $logbookService->todayDateString();
        $internId = (int) $this->user()?->id;
        $maxSizeKb = (int) config('wantrace.uploads.logbook_evidence.max_size_kb', 5120);
        $allowedMimes = implode(',', config('wantrace.uploads.logbook_evidence.mimes', ['jpg', 'jpeg', 'png', 'webp']));

        /** @var Logbook|null $logbook */
        $logbook = $this->route('logbook');
        $hasExistingEvidence = $logbook instanceof Logbook && $logbook->evidence_media_id !== null;

        $resultRules = ['nullable', 'string', 'max:2000'];
        $evidenceRules = ['nullable', 'file', 'image', 'mimes:'.$allowedMimes, 'max:'.$maxSizeKb];

        if (! $hasExistingEvidence) {
            $resultRules[] = 'required_without:evidence_file';
            $evidenceRules[] = 'required_without:result';
        }

        return [
            'log_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.$earliest,
                'before_or_equal:'.$today,
                function (string $attribute, mixed $value, Closure $fail) use ($logbookService) {
                    if (is_string($value) && ! $logbookService->isAllowedLogbookDate($value)) {
                        $fail('Tanggal logbook tidak valid menurut aturan kalender kerja.');
                    }
                },
            ],
            'related_task_id' => [
                'nullable',
                'integer',
                Rule::exists('tasks', 'id')->where('intern_user_id', $internId),
            ],
            'target_pic_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'pic')->where('is_active', true);
                }),
            ],
            'title' => ['required', 'string', 'max:150'],
            'activity_description' => ['nullable', 'string', 'max:5000'],
            'activity_status' => ['nullable', 'string', Rule::in(LogbookActivityStatus::values())],
            'result' => $resultRules,
            'evidence_file' => $evidenceRules,
            'evidence_media_id' => ['prohibited'],
            'source' => ['prohibited'],
            'task_id' => ['prohibited'],
            'status' => ['prohibited'],
            'reviewed_by' => ['prohibited'],
            'reviewed_at' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new StoreLogbookRequest)->messages();
    }
}
