<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    /**
     * Menampilkan daftar tugas sesuai role:
     * - PIC: tugas yang dibuatnya + filter status/overdue/peserta
     * - Intern: tugas yang ditugaskan kepadanya
     * - Admin: monitoring seluruh tugas (read-only)
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        Gate::authorize('viewAny', Task::class);

        $query = Task::query()
            ->with(['pic:id,name,email', 'intern:id,name,email'])
            ->visibleTo($user);

        // Filter opsional: pencarian kata kunci pada title atau description
        if ($request->filled('search')) {
            $keyword = trim($request->string('search')->toString());
            if ($keyword !== '') {
                $escaped = addcslashes($keyword, '%_\\');
                $query->where(function ($q) use ($escaped) {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('description', 'like', "%{$escaped}%");
                });
            }
        }

        // Filter opsional: status
        $allowedStatuses = array_map(fn (TaskStatus $s) => $s->value, TaskStatus::cases());
        if ($request->filled('status') && in_array($request->string('status')->toString(), $allowedStatuses, true)) {
            $query->where('status', $request->string('status')->toString());
        }

        // Filter opsional: overdue
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        // Filter opsional: peserta (untuk PIC & Admin)
        if (! $user->isIntern() && $request->filled('intern_id')) {
            $query->where('intern_user_id', (int) $request->input('intern_id'));
        }

        $tasks = $query
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Ringkasan metrik sesuai cakupan visibilitas user
        $baseSummary = Task::query()->visibleTo($user);
        $summary = [
            'total' => (clone $baseSummary)->count(),
            'assigned' => (clone $baseSummary)->whereIn('status', [TaskStatus::Assigned->value, TaskStatus::InProgress->value])->count(),
            'awaiting_review' => (clone $baseSummary)->whereIn('status', [TaskStatus::Submitted->value, TaskStatus::Resubmitted->value])->count(),
            'revision_requested' => (clone $baseSummary)->where('status', TaskStatus::RevisionRequested->value)->count(),
            'accepted' => (clone $baseSummary)->where('status', TaskStatus::Accepted->value)->count(),
            'overdue' => (clone $baseSummary)->overdue()->count(),
        ];

        // Daftar peserta untuk filter / pembuatan tugas
        $internOptions = collect();
        if ($user->isAdmin()) {
            $internOptions = User::query()
                ->where('role', 'intern')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        } elseif ($user->isPic()) {
            $internOptions = $this->taskService->getActiveAssignedInternsForPic($user->id);
        }

        return view('tasks.index', [
            'tasks' => $tasks,
            'summary' => $summary,
            'internOptions' => $internOptions,
            'statusOptions' => TaskStatus::cases(),
            'defaultDeadline' => $this->taskService->now()->addDays(3)->format('Y-m-d\TH:i'),
        ]);
    }

    /**
     * Menampilkan halaman form buat tugas baru (khusus PIC).
     */
    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        Gate::authorize('create', Task::class);

        return view('tasks.create', [
            'internOptions' => $this->taskService->getActiveAssignedInternsForPic($user->id),
            'defaultDeadline' => $this->taskService->now()->addDays(3)->format('Y-m-d\TH:i'),
        ]);
    }

    /**
     * Menyimpan tugas baru sekaligus membuat 1 logbook otomatis dalam DB transaction.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        /** @var User $pic */
        $pic = $request->user();

        $task = $this->taskService->createTask(
            $pic,
            $request->validated(),
            $request->file('attachment_file')
        );

        return redirect()
            ->route('pic.tasks.show', $task)
            ->with('success_title', 'Berhasil Ditambahkan')
            ->with('status', 'Tugas berhasil dibuat dengan status Ditugaskan dan otomatis tercatat pada Logbook peserta.');
    }

    /**
     * Menampilkan detail tugas (PIC, Peserta terkait, atau Admin).
     */
    public function show(Task $task): View
    {
        Gate::authorize('view', $task);

        $task->load([
            'pic:id,name,email',
            'intern:id,name,email',
            'acceptedByUser:id,name',
            'attachmentMedia',
            'assignmentLogbook',
            'submissions.evidenceMedia',
            'submissions.submitter:id,name',
            'submissions.reviews.reviewer:id,name',
        ]);

        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    /**
     * Menampilkan form edit tugas / perpanjangan deadline (khusus PIC pembuat tugas & belum accepted).
     */
    public function edit(Task $task): View
    {
        Gate::authorize('update', $task);

        $task->load(['intern:id,name,email', 'attachmentMedia']);

        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    /**
     * Memperbarui tugas / memperpanjang deadline / mengganti lampiran (tanpa menduplikasi logbook otomatis).
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->taskService->updateTask(
            $task,
            $request->validated(),
            $request->file('attachment_file'),
            $request->user()
        );

        return redirect()
            ->route('pic.tasks.show', $task)
            ->with('success_title', 'Berhasil Diperbarui')
            ->with('status', 'Detail tugas dan batas waktu (deadline) berhasil diperbarui.');
    }

    /**
     * Peserta pemilik tugas mengubah status dari assigned -> in_progress.
     * Menolak keras jika bukan pemilik, status bukan assigned, atau mencoba memanipulasi status.
     */
    public function start(Request $request, Task $task): RedirectResponse
    {
        Gate::authorize('start', $task);

        /** @var User $intern */
        $intern = $request->user();

        $this->taskService->startTask($task, $intern);

        return redirect()
            ->route('intern.tasks.show', $task)
            ->with('success_title', 'Berhasil Diperbarui')
            ->with('status', 'Status tugas berhasil diubah menjadi Sedang Dikerjakan (In Progress).');
    }
}
