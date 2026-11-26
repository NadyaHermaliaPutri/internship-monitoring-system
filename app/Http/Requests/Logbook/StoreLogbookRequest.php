<?php

namespace App\Http\Requests\Logbook;

use App\Enums\LogbookActivityStatus;
use App\Models\Logbook;
use App\Services\LogbookService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $internId = (int) $this->user()?->id;
        $maxSizeKb = (int) config('wantrace.uploads.logbook_evidence.max_size_kb', 5120);
        $allowedMimes = implode(',', config('wantrace.uploads.logbook_evidence.mimes', ['jpg', 'jpeg', 'png', 'webp']));

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
            'activity_description' => ['nullable', 'string', 'max:5000'],
            'activity_status' => ['nullable', 'string', Rule::in(LogbookActivityStatus::values())],
            'result' => ['nullable', 'string', 'max:2000', 'required_without:evidence_file'],
            'evidence_file' => ['nullable', 'file', 'image', 'mimes:'.$allowedMimes, 'max:'.$maxSizeKb, 'required_without:result'],
            // Hardening anti-tampering: cegah peserta memanipulasi source, task_id otomatis, media, atau status approval
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
        return [
            'log_date.required' => 'Tanggal kegiatan wajib diisi.',
            'log_date.date_format' => 'Format tanggal kegiatan tidak valid.',
            'log_date.after_or_equal' => 'Tanggal logbook maksimal dapat diisi mundur '.Logbook::MAX_BACKDATE_DAYS.' hari ke belakang.',
            'log_date.before_or_equal' => 'Tanggal logbook tidak boleh melebihi tanggal hari ini.',
            'related_task_id.integer' => 'Pilihan tugas terkait tidak valid.',
            'related_task_id.exists' => 'Tugas yang dipilih tidak ditemukan atau bukan milik Anda.',
            'title.required' => 'Kegiatan wajib diisi.',
            'title.max' => 'Kegiatan maksimal 150 karakter.',
            'activity_status.in' => 'Pilihan status kegiatan tidak valid.',
            'activity_description.max' => 'Uraian aktivitas maksimal 5000 karakter.',
            'result.required_without' => 'Hasil / Output Kegiatan wajib diisi (isi keterangan/link atau unggah screenshot bukti).',
            'result.max' => 'Hasil / output kegiatan maksimal 2000 karakter.',
            'evidence_file.required_without' => 'Hasil / Output Kegiatan wajib diisi (unggah screenshot bukti atau isi keterangan/link).',
            'evidence_file.file' => 'Berkas bukti tidak valid.',
            'evidence_file.image' => 'Bukti screenshot harus berupa file gambar (JPG, PNG, atau WEBP).',
            'evidence_file.mimes' => 'Format screenshot harus JPG, JPEG, PNG, atau WEBP.',
            'evidence_file.max' => 'Ukuran screenshot bukti maksimal 5 MB.',
        ];
    }
}
