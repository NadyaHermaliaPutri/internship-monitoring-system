<?php

namespace App\Http\Controllers;

use App\Enums\LogbookSource;
use App\Enums\LogbookStatus;
use App\Http\Requests\Logbook\ReviewLogbookRequest;
use App\Http\Requests\Logbook\StoreLogbookRequest;
use App\Http\Requests\Logbook\UpdateLogbookRequest;
use App\Models\Logbook;
use App\Models\User;
use App\Services\LogbookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        $filteredBase = Logbook::query()->visibleTo($user);

        // Filter opsional: pencarian kata kunci pada title atau activity_description
        if ($request->filled('search')) {
            $keyword = trim($request->string('search')->toString());
            if ($keyword !== '') {
                $escaped = addcslashes($keyword, '%_\\');
                $filteredBase->where(function ($q) use ($escaped) {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('activity_description', 'like', "%{$escaped}%");
                });
            }
        }

        // Filter opsional: status
        if ($request->filled('status') && in_array($request->string('status')->toString(), ['pending', 'approved', 'revision_requested'], true)) {
            $filteredBase->where('status', $request->string('status')->toString());
        }

        // Filter opsional: sumber
        if ($request->filled('source') && in_array($request->string('source')->toString(), ['manual', 'task_assignment'], true)) {
            $filteredBase->where('source', $request->string('source')->toString());
        }

        // Filter opsional: tanggal
        if ($request->filled('date')) {
            $filteredBase->whereDate('log_date', $request->string('date')->toString());
        }

        // Filter opsional: peserta (untuk PIC & Admin)
        if (! $user->isIntern() && $request->filled('intern_id')) {
            $filteredBase->where('intern_user_id', (int) $request->input('intern_id'));
        }

        $relations = [
            'intern:id,name,email',
            'reviewer:id,name',
            'targetPic:id,name',
            'task.pic:id,name',
            'relatedTask.pic:id,name',
            'evidenceMedia:id,owner_user_id,category,original_name,mime_type,file_size_bytes',
        ];

        $logbooks = null;
        $paginatedDates = null;
        $dailyGroups = collect();

        if ($user->isIntern()) {
            // Pagination utama antar-hari via subquery DISTINCT log_date
            // (aman di MySQL ONLY_FULL_GROUP_BY & SQLite agar COUNT(*) menghitung hari unik, bukan total baris)
            $distinctDatesSubquery = (clone $filteredBase)
                ->toBase()
                ->select('log_date')
                ->distinct();

            $datesBaseQuery = DB::query()
                ->fromSub($distinctDatesSubquery, 'logbook_dates')
                ->orderByDesc('log_date');

            $datesPerPage = 5;
            $totalDistinctDates = (clone $datesBaseQuery)->count();
            $lastDatesPage = max(1, (int) ceil($totalDistinctDates / $datesPerPage));
            $requestedDatesPage = max(1, (int) $request->integer('page', 1));
            $effectiveDatesPage = min($requestedDatesPage, $lastDatesPage);

            $paginatedDates = $datesBaseQuery
                ->paginate($datesPerPage, ['*'], 'page', $effectiveDatesPage)
                ->withQueryString();

            // Ambil seluruh catatan logbook utuh untuk tiap tanggal pada halaman aktif (tanpa pagination di dalam kartu)
            $dailyGroups = collect($paginatedDates->items())->map(function (object $dateRow) use ($filteredBase, $relations) {
                $dateString = Carbon::parse((string) $dateRow->log_date, Logbook::TIMEZONE)->toDateString();

                $dailyLogs = (clone $filteredBase)
                    ->whereDate('log_date', $dateString)
                    ->with($relations)
                    ->withCount('revisions')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->get();

                $manualCount = $dailyLogs->where('source', LogbookSource::Manual)->count();
                $taskCount = $dailyLogs->where('source', LogbookSource::TaskAssignment)->count();

                return [
                    'date' => $dateString,
                    'carbon' => Carbon::parse($dateString, Logbook::TIMEZONE),
                    'logs' => $dailyLogs,
                    'manual_count' => $manualCount,
                    'task_count' => $taskCount,
                ];
            });
        } else {
            $logbooks = (clone $filteredBase)
                ->with($relations)
                ->withCount('revisions')
                ->orderByDesc('log_date')
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString();
        }

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

        $picOptions = $user->isIntern()
            ? User::query()->where('role', 'pic')->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('logbooks.index', [
            'logbooks' => $logbooks,
            'dailyGroups' => $dailyGroups,
            'paginatedDates' => $paginatedDates,
            'summary' => $summary,
            'internOptions' => $internOptions,
            'picOptions' => $picOptions,
            'todayDate' => $this->logbookService->todayDateString(),
            'earliestDate' => $this->logbookService->earliestAllowedDateString(),
            'maxBackdateDays' => Logbook::MAX_BACKDATE_DAYS,
        ]);
    }

    /**
     * Form tersendiri untuk membuat logbook baru (khusus Peserta, fallback dari modal).
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Logbook::class);

        $picOptions = User::query()
            ->where('role', 'pic')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('logbooks.create', [
            'picOptions' => $picOptions,
            'todayDate' => $this->logbookService->todayDateString(),
            'earliestDate' => $this->logbookService->earliestAllowedDateString(),
            'maxBackdateDays' => Logbook::MAX_BACKDATE_DAYS,
        ]);
    }

    /**
     * Menyimpan logbook harian baru milik peserta.
     */
    public function store(StoreLogbookRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $created = $this->logbookService->createManualEntry(
            $user,
            $request->validated(),
            $request->file('evidence_file')
        );

        $message = $created->target_pic_user_id !== null
            ? 'Logbook harian berhasil disimpan sebagai Sedang Dikerjakan. Klik Selesai bila kegiatan sudah rampung agar masuk antrian review PIC.'
            : 'Logbook kegiatan mandiri berhasil disimpan sebagai Sedang Dikerjakan.';

        return redirect()
            ->route('intern.logbooks.index')
            ->with('success_title', 'Berhasil Ditambahkan')
            ->with('status', $message);
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
            'targetPic:id,name',
            'task.pic:id,name',
            'relatedTask.pic:id,name',
            'evidenceMedia',
            'revisions.editor:id,name',
            'revisions.evidenceMedia',
        ]);

        return view('logbooks.show', [
            'logbook' => $logbook,
        ]);
    }

    /**
     * Menampilkan form edit logbook (hanya pemilik & belum approved & source manual).
     */
    public function edit(Request $request, Logbook $logbook): View
    {
        Gate::authorize('update', $logbook);

        $logbook->load(['revisions', 'evidenceMedia']);

        $picOptions = User::query()
            ->where('role', 'pic')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('logbooks.edit', [
            'logbook' => $logbook,
            'picOptions' => $picOptions,
            'todayDate' => $this->logbookService->todayDateString(),
            'earliestDate' => $this->logbookService->earliestAllowedDateString(),
            'maxBackdateDays' => Logbook::MAX_BACKDATE_DAYS,
        ]);
    }

    /**
     * Memperbarui logbook harian peserta dan menyimpan snapshot ke logbook_revisions.
     */
    public function update(UpdateLogbookRequest $request, Logbook $logbook): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->logbookService->updateManualEntry(
            $logbook,
            $user,
            $request->validated(),
            $request->file('evidence_file')
        );

        return redirect()
            ->route('intern.logbooks.show', $logbook)
            ->with('success_title', 'Berhasil Diperbarui')
            ->with('status', 'Perubahan logbook berhasil disimpan dan riwayat revisi telah tercatat.');
    }

    /**
     * Menandai progres kegiatan manual peserta menjadi Selesai:
     * - Bila ditujukan ke PIC -> status berubah ke pending (Menunggu Review PIC).
     * - Bila Mandiri -> status tetap null (Selesai).
     */
    public function complete(Request $request, Logbook $logbook): RedirectResponse
    {
        Gate::authorize('complete', $logbook);

        /** @var User $user */
        $user = $request->user();

        $completed = $this->logbookService->completeManualEntry($logbook, $user);

        $message = $completed->target_pic_user_id !== null
            ? 'Kegiatan berhasil ditandai Selesai dan kini masuk antrian Menunggu Review PIC.'
            : 'Kegiatan mandiri berhasil ditandai Selesai.';

        return redirect()
            ->back(fallback: route('intern.logbooks.index'))
            ->with('success_title', 'Kegiatan Selesai')
            ->with('status', $message);
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
            ->with('success_title', 'Review Berhasil Disimpan')
            ->with('status', $message);
    }
}
