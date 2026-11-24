<?php

namespace App\Http\Controllers;

use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Http\Requests\Logbook\ReviewLogbookRequest;
use App\Http\Requests\Logbook\StoreLogbookRequest;
use App\Http\Requests\Logbook\UpdateLogbookRequest;
use App\Models\Logbook;
use App\Models\Task;
use App\Models\User;
use App\Services\LogbookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LogbookController extends Controller
{
    public function __construct(
        private readonly LogbookService $logbookService
    ) {}

    /**
     * Menampilkan halaman riwayat logbook sesuai cakupan role:
     * - Intern: logbook miliknya sendiri
     * - PIC: logbook terkait tugasnya + logbook mandiri peserta yang terkait dengannya
     * - Admin: seluruh logbook peserta
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        Gate::authorize('viewAny', Logbook::class);

        $query = Logbook::query()
            ->with([
                'intern:id,name,email',
                'reviewer:id,name',
                'task.pic:id,name',
                'relatedTask.pic:id,name',
            ])
            ->withCount('revisions')
            ->visibleTo($user);

        // Filter opsional: pencarian kata kunci pada title atau activity_description
        if ($request->filled('search')) {
            $keyword = trim($request->string('search')->toString());
            if ($keyword !== '') {
                $escaped = addcslashes($keyword, '%_\\');
                $query->where(function ($q) use ($escaped) {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('activity_description', 'like', "%{$escaped}%");
                });
            }
        }

        // Filter opsional: status
        if ($request->filled('status') && in_array($request->string('status')->toString(), ['pending', 'approved', 'revision_requested'], true)) {
            $query->where('status', $request->string('status')->toString());
        }

        // Filter opsional: sumber
        if ($request->filled('source') && in_array($request->string('source')->toString(), ['manual', 'task_assignment'], true)) {
            $query->where('source', $request->string('source')->toString());
        }

        // Filter opsional: tanggal
        if ($request->filled('date')) {
            $query->whereDate('log_date', $request->string('date')->toString());
        }

        // Filter opsional: peserta (untuk PIC & Admin)
        if (! $user->isIntern() && $request->filled('intern_id')) {
            $query->where('intern_user_id', (int) $request->input('intern_id'));
        }

        $logbooks = $query
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Ringkasan metrik sesuai cakupan visibilitas user
        $baseSummary = Logbook::query()->visibleTo($user);
        $summary = [
            'total_manual' => (clone $baseSummary)->where('source', LogbookSource::Manual->value)->count(),
            'pending' => (clone $baseSummary)->where('source', LogbookSource::Manual->value)->where('status', LogbookStatus::Pending->value)->count(),
            'approved' => (clone $baseSummary)->where('source', LogbookSource::Manual->value)->where('status', LogbookStatus::Approved->value)->count(),
            'revision_requested' => (clone $baseSummary)->where('source', LogbookSource::Manual->value)->where('status', LogbookStatus::RevisionRequested->value)->count(),
            'task_assignments' => (clone $baseSummary)->where('source', LogbookSource::TaskAssignment->value)->count(),
        ];

        // Daftar peserta untuk dropdown filter (khusus PIC & Admin)
        $internOptions = collect();
        if ($user->isAdmin()) {
            $internOptions = User::query()
                ->where('role', 'intern')
                ->orderBy('name')
                ->get(['id', 'name']);
        } elseif ($user->isPic()) {
            $internOptions = User::query()
                ->where('role', 'intern')
                ->where(function ($q) use ($user) {
                    $q->whereIn('id', function ($sub) use ($user) {
                        $sub->select('intern_user_id')
                            ->from('pic_assignments')
                            ->where('pic_user_id', $user->id);
                    })->orWhereIn('id', function ($sub) use ($user) {
                        $sub->select('intern_user_id')
                            ->from('tasks')
                            ->where('pic_user_id', $user->id);
                    });
                })
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('logbooks.index', [
            'logbooks' => $logbooks,
            'summary' => $summary,
            'internOptions' => $internOptions,
            'todayDate' => $this->logbookService->todayDateString(),
            'earliestDate' => $this->logbookService->earliestAllowedDateString(),
            'maxBackdateDays' => Logbook::MAX_BACKDATE_DAYS,
        ]);
    }

    /**
     * Form tersendiri untuk membuat logbook baru (khusus Peserta).
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Logbook::class);

        /** @var User $intern */
        $intern = $request->user();

        $taskOptions = Task::query()
            ->with('pic:id,name')
            ->where('intern_user_id', $intern->id)
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get(['id', 'pic_user_id', 'title', 'status']);

        return view('logbooks.create', [
            'todayDate' => $this->logbookService->todayDateString(),
            'earliestDate' => $this->logbookService->earliestAllowedDateString(),
            'maxBackdateDays' => Logbook::MAX_BACKDATE_DAYS,
            'taskOptions' => $taskOptions,
        ]);
    }

    /**
     * Menyimpan logbook harian baru milik peserta.
     */
    public function store(StoreLogbookRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->logbookService->createManualEntry($user, $request->validated());

        return redirect()
            ->route('intern.logbooks.index')
            ->with('status', 'Logbook harian berhasil disimpan dengan status Menunggu Review.');
    }

    /**
     * Menampilkan detail logbook beserta riwayat revisinya.
     */
    public function show(Logbook $logbook): View
    {
        Gate::authorize('view', $logbook);

        $logbook->load([
            'intern:id,name,email',
            'reviewer:id,name',
            'task.pic:id,name',
            'relatedTask.pic:id,name',
            'revisions.editor:id,name',
        ]);

        return view('logbooks.show', [
            'logbook' => $logbook,
        ]);
    }

    /**
     * Menampilkan form edit logbook (hanya pemilik & status pending/revision_requested & source manual).
     */
    public function edit(Request $request, Logbook $logbook): View
    {
        Gate::authorize('update', $logbook);

        /** @var User $intern */
        $intern = $request->user();

        $logbook->load('revisions');

        $taskOptions = Task::query()
            ->with('pic:id,name')
            ->where('intern_user_id', $intern->id)
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get(['id', 'pic_user_id', 'title', 'status']);

        return view('logbooks.edit', [
            'logbook' => $logbook,
            'todayDate' => $this->logbookService->todayDateString(),
            'earliestDate' => $this->logbookService->earliestAllowedDateString(),
            'maxBackdateDays' => Logbook::MAX_BACKDATE_DAYS,
            'taskOptions' => $taskOptions,
        ]);
    }

    /**
     * Memperbarui logbook harian peserta dan menyimpan snapshot ke logbook_revisions.
     */
    public function update(UpdateLogbookRequest $request, Logbook $logbook): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->logbookService->updateManualEntry($logbook, $user, $request->validated());

        return redirect()
            ->route('intern.logbooks.show', $logbook)
            ->with('status', 'Perubahan logbook berhasil disimpan dan riwayat revisi telah tercatat.');
    }

    /**
     * Meninjau (menyetujui atau meminta revisi) logbook manual peserta oleh PIC yang berwenang.
     */
    public function review(ReviewLogbookRequest $request, Logbook $logbook): RedirectResponse
    {
        /** @var User $pic */
        $pic = $request->user();

        $reviewed = $this->logbookService->reviewLogbook($logbook, $pic, $request->validated());

        $message = $reviewed->status === LogbookStatus::Approved
            ? 'Logbook harian peserta berhasil disetujui (Approved) dan telah dikunci.'
            : 'Permintaan revisi logbook beserta catatan berhasil dikirim kepada peserta.';

        return redirect()
            ->route('pic.logbooks.show', $logbook)
            ->with('status', $message);
    }
}
