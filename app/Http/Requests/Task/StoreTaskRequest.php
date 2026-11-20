<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use App\Services\TaskService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Throwable;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(TaskService $taskService): array
    {
        $picId = (int) $this->user()?->id;

        return [
            'intern_user_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($taskService, $picId) {
                    if (! is_numeric($value) || ! $taskService->isInternAssignableByPic($picId, (int) $value)) {
                        $fail('Peserta yang dipilih harus berstatus aktif dan berada dalam penugasan aktif Anda.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'deadline_at' => [
                'required',
                'date',
                function (string $attribute, mixed $value, Closure $fail) use ($taskService) {
                    if (! is_string($value)) {
                        $fail('Format batas waktu (deadline) tidak valid.');

                        return;
                    }

                    try {
                        $deadline = Carbon::parse($value, Task::TIMEZONE);
                    } catch (Throwable) {
                        $fail('Format batas waktu (deadline) tidak valid.');

                        return;
                    }

                    if ($deadline->lte($taskService->now())) {
                        $fail('Batas waktu (deadline) wajib berada di masa depan (WIB).');
                    }
                },
            ],
            // Hardening anti-tampering: tolak eksplisit percobaan menyisipkan kolom status/audit lewat payload HTTP
            'status' => ['prohibited'],
            'accepted_at' => ['prohibited'],
            'accepted_by' => ['prohibited'],
            'pic_user_id' => ['prohibited'],
            'assigned_at' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'intern_user_id.required' => 'Peserta magang penerima tugas wajib dipilih.',
            'intern_user_id.integer' => 'Pilihan peserta magang tidak valid.',
            'title.required' => 'Judul tugas wajib diisi.',
            'title.max' => 'Judul tugas maksimal 180 karakter.',
            'description.required' => 'Deskripsi tugas wajib diisi.',
            'description.min' => 'Deskripsi tugas minimal 10 karakter agar instruksi jelas.',
            'description.max' => 'Deskripsi tugas maksimal 5000 karakter.',
            'deadline_at.required' => 'Batas waktu pengumpulan (deadline) wajib diisi.',
            'deadline_at.date' => 'Format batas waktu pengumpulan tidak valid.',
        ];
    }
}
