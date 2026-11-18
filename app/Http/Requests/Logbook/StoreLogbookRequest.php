<?php

namespace App\Http\Requests\Logbook;

use App\Models\Logbook;
use App\Services\LogbookService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Logbook::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(LogbookService $logbookService): array
    {
        $earliest = $logbookService->earliestAllowedDateString();
        $today = $logbookService->todayDateString();

        return [
            'log_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.$earliest,
                'before_or_equal:'.$today,
                function (string $attribute, mixed $value, \Closure $fail) use ($logbookService) {
                    if (is_string($value) && ! $logbookService->isAllowedLogbookDate($value)) {
                        $fail('Tanggal logbook tidak valid menurut aturan kalender kerja.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:150'],
            'activity_description' => ['required', 'string', 'min:10', 'max:5000'],
            'result' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'log_date.required' => 'Tanggal kegiatan wajib diisi.',
            'log_date.date_format' => 'Format tanggal kegiatan tidak valid.',
            'log_date.after_or_equal' => 'Tanggal logbook maksimal dapat diisi mundur '.Logbook::MAX_BACKDATE_DAYS.' hari ke belakang.',
            'log_date.before_or_equal' => 'Tanggal logbook tidak boleh melebihi tanggal hari ini (WIB).',
            'title.required' => 'Judul kegiatan wajib diisi.',
            'title.max' => 'Judul kegiatan maksimal 150 karakter.',
            'activity_description.required' => 'Uraian aktivitas wajib diisi.',
            'activity_description.min' => 'Uraian aktivitas minimal 10 karakter agar deskripsi jelas.',
            'activity_description.max' => 'Uraian aktivitas maksimal 5000 karakter.',
            'result.max' => 'Hasil / output kegiatan maksimal 2000 karakter.',
        ];
    }
}
