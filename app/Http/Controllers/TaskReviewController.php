<?php

namespace App\Http\Controllers;

use App\Enums\TaskReviewDecision;
use App\Http\Requests\Task\StoreTaskReviewRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;

class TaskReviewController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    /**
     * Menyimpan hasil peninjauan (review) PIC terhadap pengumpulan terbaru tugas peserta.
     */
    public function store(StoreTaskReviewRequest $request, Task $task): RedirectResponse
    {
        /** @var User $pic */
        $pic = $request->user();

        $review = $this->taskService->reviewTask(
            task: $task,
            pic: $pic,
            data: $request->validated()
        );

        $message = $review->decision === TaskReviewDecision::Accepted
            ? 'Tugas berhasil diterima (Accepted) dan dinyatakan selesai.'
            : 'Permintaan revisi beserta catatan feedback berhasil dikirim kepada peserta.';

        return redirect()
            ->route('pic.tasks.show', $task)
            ->with('status', $message);
    }
}
