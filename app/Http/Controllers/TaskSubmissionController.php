<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Task\StoreTaskSubmissionRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;

class TaskSubmissionController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    /**
     * Menyimpan pengumpulan / pengumpulan ulang hasil tugas oleh peserta magang.
     */
    public function store(StoreTaskSubmissionRequest $request, Task $task): RedirectResponse
    {
        /** @var User $intern */
        $intern = $request->user();

        $submission = $this->taskService->submitTask(
            task: $task,
            intern: $intern,
            data: $request->validated(),
            evidenceFile: $request->file('evidence_file')
        );

        $task->refresh();
        $statusLabel = $task->status === TaskStatus::Resubmitted
            ? 'Dikumpulkan Ulang (Resubmitted)'
            : 'Menunggu Review (Submitted)';

        return redirect()
            ->route('intern.tasks.show', $task)
            ->with('success_title', 'Pengumpulan Berhasil')
            ->with('status', "Pengumpulan tugas Ke-{$submission->submission_number} berhasil dikirim. Status tugas kini: {$statusLabel}.");
    }
}
