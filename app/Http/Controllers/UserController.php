<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar seluruh akun pengguna dengan pencarian dan filter.
     * Otorisasi: Hanya Admin (via UserPolicy::viewAny).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        $query = User::with(['internProfile', 'picProfile', 'picAssignments']);

        // 1. Filter Tab (Semua, Aktif, Intern, PIC, Nonaktif)
        $tab = $request->input('tab', 'all');
        if ($tab === 'nonactive') {
            $tab = 'inactive';
        }

        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'intern') {
            $query->where('role', 'intern')->where('is_active', true);
        } elseif ($tab === 'pic') {
            $query->where('role', 'pic')->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        }

        // 2. Filter Pencarian Nama, Email, atau Role
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        // 3. Filter Spesifik Tambahan (jika ada dropdown role/status)
        if ($role = $request->input('role')) {
            if (in_array($role, ['admin', 'pic', 'intern'], true)) {
                $query->where('role', $role);
            }
        }
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Statistik Ringkas Akun Sesuai Desain Figma (Dinamis dari Database)
        $now = \Carbon\Carbon::now();
        $stats = [
            'total_all'        => User::count(),
            'total_active'     => User::where('is_active', true)->count(),
            'intern_count'     => User::where('role', 'intern')->where('is_active', true)->count(),
            'pic_count'        => User::where('role', 'pic')->where('is_active', true)->count(),
            'inactive_count'   => User::where('is_active', false)->count(),
            'tab_intern'       => User::where('role', 'intern')->where('is_active', true)->count(),
            'tab_pic'          => User::where('role', 'pic')->where('is_active', true)->count(),
            'tab_inactive'     => User::where('is_active', false)->count(),
            'near_end_count'   => User::where('role', 'intern')
                                    ->where('is_active', true)
                                    ->whereHas('internProfile', function ($q) use ($now) {
                                        $q->whereBetween('internship_end_date', [$now, $now->copy()->addDays(30)]);
                                    })
                                    ->count(),
            'pic_departments'  => 'Pembimbing Magang',
        ];

        // Urutkan berdasarkan ID/terbaru dan paginasi
        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users', 'stats', 'tab'));
    }

    /**
     * Menampilkan formulir pendaftaran akun pengguna baru oleh Admin/HR.
     * Otorisasi: Hanya Admin (via UserPolicy::create).
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create');
    }

    /**
     * Menyimpan akun pengguna baru ke database.
     * Otorisasi: Hanya Admin (via UserPolicy::create).
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun untuk {$user->name} ({$user->role}) berhasil dibuat.");
    }

    /**
     * Menampilkan formulir edit akun pengguna.
     * Otorisasi: Admin (semua akun) atau Intern (hanya akun sendiri) via UserPolicy::update.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Memperbarui informasi akun pengguna.
     * Otorisasi: Admin (semua akun) atau Intern (hanya akun sendiri) via UserPolicy::update.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $validated = $request->validated();
        $currentUser = Auth::user();

        // Keamanan: Cegah Admin menonaktifkan akunnya sendiri saat sedang login
        if ($currentUser && $currentUser->id === $user->id && ! $validated['is_active']) {
            return back()
                ->withInput()
                ->withErrors(['is_active' => 'Anda tidak dapat menonaktifkan akun Anda sendiri saat sedang aktif masuk.']);
        }

        // Keamanan: Cegah Admin menurunkan peran (role) akunnya sendiri menjadi non-admin
        if ($currentUser && $currentUser->id === $user->id && $validated['role'] !== 'admin') {
            return back()
                ->withInput()
                ->withErrors(['role' => 'Anda tidak dapat mengubah peran (role) akun Anda sendiri menjadi non-admin.']);
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'],
        ];

        // Perbarui password hanya jika input password diisi
        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data akun {$user->name} berhasil diperbarui.");
    }

    /**
     * Mengubah status aktif/nonaktif akun pengguna secara instan (Toggle Status).
     * Sesuai aturan bisnis: Akun yang selesai masa magang di-nonaktifkan, BUKAN dihapus.
     * Otorisasi: Hanya Admin (via UserPolicy::toggleStatus).
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('toggleStatus', $user);
        $currentUser = Auth::user();

        if ($currentUser && $currentUser->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Status akun {$user->name} berhasil {$statusText}.");
    }

    /**
     * Catatan Integritas Data PT WAN Teknologi Internasional:
     * Metode destroy() sengaja ditiadakan karena akun peserta atau staf yang sudah
     * memiliki relasi historis (presensi, logbook, tugas, assignment) TIDAK BOLEH
     * dihapus fisik (hard delete), melainkan di-nonaktifkan melalui is_active = false.
     */
}
