<?php

namespace App\Http\Requests\Logbook;

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
            'title' => ['required', 'string', 'max:150'],
            'activity_description' => ['required', 'string', 'min:10', 'max:5000'],
            'result' => ['nullable', 'string', 'max:2000'],
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
