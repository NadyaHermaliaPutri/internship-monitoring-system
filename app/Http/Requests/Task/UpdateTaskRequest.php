<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use App\Services\TaskService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Throwable;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Task|null $task */
        $task = $this->route('task');

        return $task && ($this->user()?->can('update', $task) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(TaskService $taskService): array
    {
        return [
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new StoreTaskRequest)->messages();
    }
}
