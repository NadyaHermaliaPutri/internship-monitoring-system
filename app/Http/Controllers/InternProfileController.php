<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInternProfileRequest;
use App\Models\InternProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternProfileController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar profil peserta magang lengkap dengan status pengisian data.
     * Otorisasi: Hanya Admin dan PIC yang diizinkan (via InternProfilePolicy::viewAny).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InternProfile::class);

        $query = User::where('role', 'intern')->with('internProfile');

        // Pencarian (Nama, Email, Institusi, atau Kode Peserta)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('internProfile', function ($profileQuery) use ($search) {
                      $profileQuery->where('institution', 'like', "%{$search}%")
                                   ->orWhere('study_program', 'like', "%{$search}%")
                                   ->orWhere('intern_code', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Status Kelengkapan Profil & Status Aktif
        $filter = $request->input('filter', 'all');
        if ($filter === 'completed') {
            $filter = 'complete';
        }

        if ($filter === 'complete') {
            $query->whereHas('internProfile', function ($q) {
                $q->whereNotNull('institution')->whereNotNull('internship_start_date');
            });
        } elseif ($filter === 'incomplete') {
            $query->where(function ($q) {
                $q->doesntHave('internProfile')
                  ->orWhereHas('internProfile', function ($sub) {
                      $sub->whereNull('institution')->orWhereNull('internship_start_date');
                  });
            });
        } elseif ($filter === 'active') {
            $query->where('is_active', true);
        }

        // Statistik Ringkasan Profil Peserta
        $allInterns = User::where('role', 'intern')->with('internProfile')->get();
        $today = Carbon::today();

        $stats = [
            'total_interns'      => $allInterns->count(),
            'complete_profiles'  => $allInterns->filter(fn ($u) => $u->internProfile && $u->internProfile->institution && $u->internProfile->internship_start_date)->count(),
            'incomplete_profiles'=> $allInterns->filter(fn ($u) => ! $u->internProfile || ! $u->internProfile->institution || ! $u->internProfile->internship_start_date)->count(),
            'active_interns'     => $allInterns->where('is_active', true)->count(),
            'ongoing_interns'    => $allInterns->filter(function ($u) use ($today) {
                if (! $u->is_active || ! $u->internProfile?->internship_start_date) {
                    return false;
                }
                $start = $u->internProfile->internship_start_date;
                $end   = $u->internProfile->internship_end_date;

                return $start->lte($today) && (! $end || $end->gte($today));
            })->count(),
        ];

        $interns = $query->latest()->paginate(10)->withQueryString();

        $editUser = null;
        if ($editId = $request->input('edit')) {
            $editUser = User::where('role', 'intern')->with('internProfile')->find($editId);
        }

        return view('admin.intern_profiles.index', compact('interns', 'stats', 'filter', 'editUser'));
    }

    /**
     * Menampilkan form input / perbarui data profil khusus peserta magang.
     * Otorisasi: Hanya Admin yang dapat mengedit profil peserta lain.
     */
    public function edit(User $user): RedirectResponse
    {
        // Pastikan target akun benar-benar ber-role intern
        if (! $user->isIntern()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', "Pengguna {$user->name} bukan peserta magang (role: {$user->role}).");
        }

        // Otorisasi: Hanya Admin yang bisa mengakses form edit profil peserta lain
        $this->authorize('update', $user->internProfile ?? new InternProfile(['user_id' => $user->id]));

        return redirect()->route('admin.intern-profiles.index', ['edit' => $user->id]);
    }

    /**
     * Menyimpan atau memperbarui data profil peserta magang ke tabel intern_profiles.
     * Otorisasi: Admin penuh; Intern hanya untuk profilnya sendiri.
     */
    public function update(UpdateInternProfileRequest $request, User $user): RedirectResponse
    {
        // Pastikan target akun ber-role intern
        if (! $user->isIntern()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', "Gagal memproses profil: akun {$user->name} bukan peserta magang.");
        }

        // Otorisasi backend via Policy
        $internProfile = $user->internProfile ?? new InternProfile(['user_id' => $user->id]);
        $this->authorize('update', $internProfile);

        $validated = $request->validated();

        $user->internProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'institution'           => $validated['institution'] ?? null,
                'study_program'         => $validated['study_program'] ?? null,
                'internship_start_date' => $validated['internship_start_date'],
                'internship_end_date'   => $validated['internship_end_date'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.intern-profiles.index')
            ->with('success', "Profil magang untuk peserta {$user->name} berhasil diperbarui.");
    }
}
