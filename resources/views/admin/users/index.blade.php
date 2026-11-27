@extends('layouts.app')

@section('title', 'Kelola Akun Pengguna')

@section('page-title', 'Kelola Akun Pengguna')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Pengelolaan Hak Akses & Penugasan PIC')

@section('custom-header-right')
    <!-- Widget Tanggal -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-white px-3.5 py-2.5 rounded-xl border border-slate-200/80 shadow-xs">
        <svg class="w-3.5 h-3.5 text-[#015987]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, j F Y') }}</span>
    </div>

    <!-- Search Input Form -->
    <form method="GET" action="{{ route('admin.users.index') }}" class="relative hidden sm:block">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input 
            type="text" 
            name="search" 
            value="{{ request('search') }}"
            placeholder="Cari nama, email, atau role..." 
            class="w-64 pl-9 pr-3.5 py-2.5 text-xs bg-white border border-slate-200/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] shadow-xs transition"
        >
        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </form>
@endsection

@section('content')
<div 
    x-data="userManagementApp()" 
    x-cloak 
    @open-create-user-modal.window="openCreateModal()"
    class="space-y-6"
>

    <!-- ================================================================= -->
    <!-- 1. KARTU STATISTIK 4 KOLOM PERSIS SESUAI MOCKUP                   -->
    <!-- ================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Kartu 1: Total Akun Aktif -->
        <a 
            href="{{ route('admin.users.index', ['tab' => 'active', 'search' => request('search')]) }}"
            class="bg-white rounded-2xl p-5 flex flex-col justify-between min-h-[136px] transition-all duration-200 cursor-pointer group {{ $tab === 'active' ? 'border-2 border-sky-500 ring-4 ring-sky-500/10 shadow-md' : 'border border-slate-200/80 shadow-xs hover:border-sky-300 hover:shadow-md hover:-translate-y-0.5' }}"
        >
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold {{ $tab === 'active' ? 'text-sky-700' : 'text-slate-700 group-hover:text-sky-700' }} transition-colors">Total Akun Aktif</span>
                <div class="w-8 h-8 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center {{ $tab === 'active' ? 'ring-2 ring-sky-200' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['total_active'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">Akun</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-sky-600 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                    <span>Dapat login ke sistem</span>
                </div>
            </div>
        </a>

        <!-- Kartu 2: Peserta Magang (Intern) -->
        <a 
            href="{{ route('admin.users.index', ['tab' => 'intern', 'search' => request('search')]) }}"
            class="bg-white rounded-2xl p-5 flex flex-col justify-between min-h-[136px] transition-all duration-200 cursor-pointer group {{ $tab === 'intern' ? 'border-2 border-teal-500 ring-4 ring-teal-500/10 shadow-md' : 'border border-slate-200/80 shadow-xs hover:border-teal-300 hover:shadow-md hover:-translate-y-0.5' }}"
        >
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold {{ $tab === 'intern' ? 'text-teal-700' : 'text-slate-700 group-hover:text-teal-700' }} transition-colors">Peserta Magang (Intern)</span>
                <div class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center {{ $tab === 'intern' ? 'ring-2 ring-teal-200' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['intern_count'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">Peserta</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-teal-600 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    <span>{{ $stats['near_end_count'] }} mendekati akhir masa</span>
                </div>
            </div>
        </a>

        <!-- Kartu 3: PIC Mentor -->
        <a 
            href="{{ route('admin.users.index', ['tab' => 'pic', 'search' => request('search')]) }}"
            class="bg-white rounded-2xl p-5 flex flex-col justify-between min-h-[136px] transition-all duration-200 cursor-pointer group {{ $tab === 'pic' ? 'border-2 border-purple-500 ring-4 ring-purple-500/10 shadow-md' : 'border border-slate-200/80 shadow-xs hover:border-purple-300 hover:shadow-md hover:-translate-y-0.5' }}"
        >
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold {{ $tab === 'pic' ? 'text-purple-700' : 'text-slate-700 group-hover:text-purple-700' }} transition-colors">PIC Mentor</span>
                <div class="w-8 h-8 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center {{ $tab === 'pic' ? 'ring-2 ring-purple-200' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['pic_count'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">PIC</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-purple-600 font-medium truncate">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500 flex-shrink-0"></span>
                    <span class="truncate">{{ $stats['pic_departments'] }}</span>
                </div>
            </div>
        </a>

        <!-- Kartu 4: Akun Dinonaktifkan -->
        <a 
            href="{{ route('admin.users.index', ['tab' => 'nonactive', 'search' => request('search')]) }}"
            class="bg-white rounded-2xl p-5 flex flex-col justify-between min-h-[136px] transition-all duration-200 cursor-pointer group {{ in_array($tab, ['inactive', 'nonactive'], true) ? 'border-2 border-rose-500 ring-4 ring-rose-500/10 shadow-md' : 'border border-slate-200/80 shadow-xs hover:border-rose-300 hover:shadow-md hover:-translate-y-0.5' }}"
        >
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold {{ in_array($tab, ['inactive', 'nonactive'], true) ? 'text-rose-700' : 'text-slate-700 group-hover:text-rose-700' }} transition-colors">Akun Dinonaktifkan</span>
                <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center {{ in_array($tab, ['inactive', 'nonactive'], true) ? 'ring-2 ring-rose-200' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['inactive_count'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">Alumni</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-slate-400 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                    <span>Histori data tetap utuh</span>
                </div>
            </div>
        </a>

    </div>

    <!-- ================================================================= -->
    <!-- 2. CARD TABEL UTAMA PERSIS SESUAI MOCKUP                          -->
    <!-- ================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Header Dalam Card: Split Layout (Kiri: Judul & Filter Bertumpuk, Kanan: Tombol Tambah Akun Besar) -->
        <div class="p-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 border-b border-slate-100">
            <!-- Sisi Sebelah Kiri: Judul & Filter Tab Bertumpuk -->
            <div class="space-y-3.5">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Daftar Akun Pengguna</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Kelola status aktif, role, dan penugasan PIC pembimbing</p>
                </div>

                <!-- Deretan Tombol Filter Tab (Tepat di Bawah Judul) -->
                <div class="flex flex-wrap items-center gap-1.5 bg-slate-100/90 p-1 rounded-xl border border-slate-200/60 shadow-2xs">
                    <a 
                        href="{{ route('admin.users.index', ['tab' => 'all', 'search' => request('search')]) }}" 
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $tab === 'all' ? 'bg-[#015987] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800' }}"
                    >Semua ({{ $stats['total_all'] }})</a>
                    <a 
                        href="{{ route('admin.users.index', ['tab' => 'intern', 'search' => request('search')]) }}" 
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $tab === 'intern' ? 'bg-[#015987] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800' }}"
                    >Intern ({{ $stats['tab_intern'] }})</a>
                    <a 
                        href="{{ route('admin.users.index', ['tab' => 'pic', 'search' => request('search')]) }}" 
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $tab === 'pic' ? 'bg-[#015987] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800' }}"
                    >PIC ({{ $stats['tab_pic'] }})</a>
                    <a 
                        href="{{ route('admin.users.index', ['tab' => 'inactive', 'search' => request('search')]) }}" 
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $tab === 'inactive' ? 'bg-[#015987] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800' }}"
                    >Nonaktif ({{ $stats['tab_inactive'] }})</a>
                </div>
            </div>

            <!-- Sisi Sebelah Kanan: Tombol Utama "Tambah Akun Baru" (Besar & Menonjol) -->
            <div class="self-stretch sm:self-auto flex items-center justify-start lg:justify-end">
                <button 
                    type="button" 
                    @click="openCreateModal()"
                    class="inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-xl bg-[#014467] hover:bg-[#01334e] text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer border border-[#015987]/40 ring-2 ring-[#015987]/20 flex-shrink-0"
                >
                    <div class="w-5 h-5 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span>Tambah Akun Baru</span>
                </button>
            </div>
        </div>

        <!-- Tabel Data Sesuai Kolom Mockup -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6">Nama</th>
                        <th class="py-4 px-6">Email</th>
                        <th class="py-4 px-6">Role Sistem</th>
                        <th class="py-4 px-6">Status Akun</th>
                        <th class="py-4 px-6 text-right">Aksi Administrasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($users as $index => $user)
                        @php
                            $avatarColors = ['bg-sky-100 text-sky-700', 'bg-rose-100 text-rose-600', 'bg-purple-100 text-purple-700', 'bg-slate-200 text-slate-500'];
                            $avatarClass = $avatarColors[$index % 4];

                            // Badge role
                            if (!$user->is_active && $user->role === 'intern') {
                                $roleLabel  = 'INTERN (SELESAI)';
                                $badgeClass = 'bg-slate-100 text-slate-500 border-slate-200';
                            } elseif ($user->role === 'intern') {
                                $roleLabel  = 'INTERN';
                                $badgeClass = 'bg-sky-50 text-sky-600 border-sky-100';
                            } elseif ($user->role === 'pic') {
                                $roleLabel  = 'PIC (MENTOR)';
                                $badgeClass = 'bg-purple-50 text-purple-600 border-purple-100';
                            } else {
                                $roleLabel  = 'ADMIN / HR';
                                $badgeClass = 'bg-indigo-50 text-indigo-600 border-indigo-100';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            
                            <!-- Nama -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-xs {{ $avatarClass }}">
                                        {{ strtoupper(substr(preg_replace('/\s+/', ' ', trim($user->name)), 0, 1)) }}{{ strtoupper(substr(strstr(preg_replace('/\s+/', ' ', trim($user->name)), ' ') ?: ' ', 1, 1)) }}
                                    </div>
                                    <div class="font-semibold text-slate-900">{{ $user->name }}</div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="py-4 px-6 text-slate-500">
                                {{ $user->email }}
                            </td>

                            <!-- Role Sistem -->
                            <td class="py-4 px-6">
                                <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-extrabold tracking-wide uppercase border {{ $badgeClass }}">
                                    {{ $roleLabel }}
                                </span>
                            </td>

                            <!-- Status Akun -->
                            <td class="py-4 px-6">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        AKTIF
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        NONAKTIF
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi Administrasi -->
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    @if($user->isIntern())
                                        <!-- Tombol Lihat Detail (Role Intern -> Redirect ke Profil Peserta dengan filter nama) -->
                                        <a 
                                            href="{{ route('admin.intern-profiles.index', ['search' => $user->name]) }}" 
                                            title="Lihat Detail Profil Peserta"
                                            class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition inline-flex items-center gap-1"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Lihat Detail</span>
                                        </a>
                                    @else
                                        <!-- Tombol Lihat Detail (Role Admin & PIC -> Modal Pop-up) -->
                                        <button 
                                            type="button" 
                                            @click="openDetailModal({
                                                id: {{ $user->id }},
                                                name: '{{ addslashes($user->name) }}',
                                                email: '{{ addslashes($user->email) }}',
                                                role: '{{ $user->role }}',
                                                role_label: '{{ $user->role === "admin" ? "Admin / HR" : "PIC (Pembimbing)" }}',
                                                is_active: {{ $user->is_active ? 'true' : 'false' }},
                                                created_at: '{{ $user->created_at->translatedFormat("d F Y") }}',
                                                assigned_interns_count: {{ $user->picAssignments->count() ?? 0 }},
                                                is_self: {{ $user->id === auth()->id() ? 'true' : 'false' }}
                                            })"
                                            title="Lihat Detail Akun"
                                            class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition inline-flex items-center gap-1 cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Lihat Detail</span>
                                        </button>
                                    @endif

                                    <!-- Tombol Edit (Memicu Modal Pop-up) -->
                                    <button 
                                        type="button" 
                                        @click="openEditModal({
                                            id: {{ $user->id }},
                                            name: '{{ addslashes($user->name) }}',
                                            email: '{{ addslashes($user->email) }}',
                                            role: '{{ $user->role }}',
                                            is_active: {{ $user->is_active ? 'true' : 'false' }},
                                            is_self: {{ $user->id === auth()->id() ? 'true' : 'false' }}
                                        })"
                                        class="px-4 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer"
                                    >
                                        Edit
                                    </button>

                                    <!-- Tombol Toggle Status (Memicu Modal Konfirmasi Kustom) -->
                                    @if($user->id !== auth()->id())
                                        <button 
                                            type="button" 
                                            @click="openConfirmToggleModal({
                                                id: {{ $user->id }},
                                                name: '{{ addslashes($user->name) }}',
                                                isActive: {{ $user->is_active ? 'true' : 'false' }}
                                            })"
                                            class="px-3.5 py-1.5 rounded-lg border {{ $user->is_active ? 'border-rose-200 text-rose-500 hover:bg-rose-50' : 'border-sky-300 text-[#015987] hover:bg-sky-50' }} bg-white text-xs font-semibold transition cursor-pointer"
                                        >
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <p class="text-sm font-semibold text-slate-500">Tidak ada akun yang ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba ubah filter tab atau kata kunci pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Tabel: Info Jumlah & Panah Paginasi -->
        <div class="px-6 py-4 flex items-center justify-between border-t border-slate-100 text-[11px] text-slate-400">
            <span>
                Menampilkan {{ $users->firstItem() ?? 0 }} dari {{ $users->total() }} total akun terdaftar
            </span>
            <div class="flex items-center gap-1">
                @if($users->onFirstPage())
                    <span class="w-7 h-7 rounded-lg border border-slate-200 text-slate-300 flex items-center justify-center cursor-not-allowed select-none">&lt;</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="w-7 h-7 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 flex items-center justify-center transition">&lt;</a>
                @endif

                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="w-7 h-7 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 flex items-center justify-center transition">&gt;</a>
                @else
                    <span class="w-7 h-7 rounded-lg border border-slate-200 text-slate-300 flex items-center justify-center cursor-not-allowed select-none">&gt;</span>
                @endif
            </div>
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- MODAL 1: TAMBAH AKUN BARU                                         -->
    <!-- ================================================================= -->
    <div 
        x-show="activeModal === 'create'" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs" @click="closeModal()"></div>

        <!-- Panel Modal -->
        <div 
            @click.stop
            x-show="activeModal === 'create'"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200/80 p-7 sm:p-8 max-h-[90vh] overflow-y-auto"
        >
            <!-- Tombol Tutup X -->
            <button 
                type="button" 
                @click="closeModal()" 
                class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-100"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Judul & Subjudul -->
            <div class="mb-5 pr-8">
                <h2 class="text-xl font-bold text-slate-900">Tambah Akun Baru</h2>
                <p class="text-xs text-[#015987] font-medium mt-1">Lengkapi informasi berikut untuk menambah pengguna baru ke sistem.</p>
            </div>

            <!-- Error Validasi Global (Khusus Modal Create) -->
            @if($errors->any() && old('_modal') === 'create')
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200/80 text-xs text-rose-700">
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_modal" value="create">
                <input type="hidden" name="is_active" value="1">

                <!-- Nama Lengkap -->
                <div>
                    <label for="create_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="create_name" 
                        name="name" 
                        value="{{ old('_modal') === 'create' ? old('name') : '' }}"
                        placeholder="Masukkan nama lengkap"
                        required
                        maxlength="150"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="create_email" class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                    <input 
                        type="email" 
                        id="create_email" 
                        name="email" 
                        value="{{ old('_modal') === 'create' ? old('email') : '' }}"
                        placeholder="contoh@gmail.com"
                        required
                        maxlength="190"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                </div>

                <!-- Kata Sandi dengan Toggle Mata -->
                <div>
                    <label for="create_password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi</label>
                    <div class="relative">
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            id="create_password" 
                            name="password" 
                            placeholder="Minimal 8 karakter"
                            required
                            minlength="8"
                            @input="$refs.createConfirmMirror.value = $event.target.value"
                            class="w-full px-3.5 py-2.5 pr-10 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                        >
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
                            tabindex="-1"
                        >
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                            <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <input type="hidden" name="password_confirmation" x-ref="createConfirmMirror">
                </div>

                <!-- Role Sistem -->
                <div>
                    <label for="create_role" class="block text-xs font-semibold text-slate-700 mb-1">Role Sistem</label>
                    <div class="relative">
                        <select 
                            id="create_role" 
                            name="role" 
                            required
                            class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition pr-10"
                        >
                            <option value="" disabled selected>Pilih Role</option>
                            <option value="intern" {{ old('_modal') === 'create' && old('role') === 'intern' ? 'selected' : '' }}>Peserta Magang (Intern)</option>
                            <option value="pic" {{ old('_modal') === 'create' && old('role') === 'pic' ? 'selected' : '' }}>PIC Mentor</option>
                            <option value="admin" {{ old('_modal') === 'create' && old('role') === 'admin' ? 'selected' : '' }}>Admin / HR</option>
                        </select>
                        <div class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-3 pt-2">
                    <button 
                        type="button" 
                        @click="closeModal()" 
                        class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="flex-1 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs shadow-xs hover:shadow-md transition cursor-pointer"
                    >
                        Tambah Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL 2: EDIT AKUN PENGGUNA                                       -->
    <!-- ================================================================= -->
    <div 
        x-show="activeModal === 'edit'" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs" @click="closeModal()"></div>

        <!-- Panel Modal -->
        <div 
            @click.stop
            x-show="activeModal === 'edit'"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200/80 p-7 sm:p-8 max-h-[90vh] overflow-y-auto"
        >
            <!-- Tombol Tutup X -->
            <button 
                type="button" 
                @click="closeModal()" 
                class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition p-1 rounded-lg hover:bg-slate-100"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Judul & Subjudul -->
            <div class="mb-5 pr-8">
                <h2 class="text-xl font-bold text-slate-900">Edit Akun Pengguna</h2>
                <p class="text-xs text-[#015987] font-medium mt-1">Perbarui informasi akun pengguna di bawah ini.</p>
            </div>

            <!-- Peringatan jika edit akun sendiri -->
            <div x-show="editUser.is_self" class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-xs text-amber-700 flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span>Anda sedang mengedit akun sendiri. Role dan status akun terkunci demi keamanan sistem.</span>
            </div>

            <!-- Error Validasi Global (Khusus Modal Edit) -->
            @if($errors->any() && old('_modal') === 'edit')
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200/80 text-xs text-rose-700">
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" :action="editUrl" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">
                <input type="hidden" name="user_id" :value="editUser.id">

                <!-- Nama Lengkap -->
                <div>
                    <label for="edit_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="edit_name" 
                        name="name" 
                        x-model="editUser.name"
                        required
                        maxlength="150"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="edit_email" class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                    <input 
                        type="email" 
                        id="edit_email" 
                        name="email" 
                        x-model="editUser.email"
                        required
                        maxlength="190"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                </div>

                <!-- Kata Sandi Baru (Opsional) -->
                <div>
                    <label for="edit_password" class="block text-xs font-semibold text-slate-700 mb-1">
                        Kata Sandi Baru <span class="font-normal text-slate-400">(opsional)</span>
                    </label>
                    <div class="relative">
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            id="edit_password" 
                            name="password" 
                            placeholder="Kosongkan jika tidak ingin mengubah password"
                            minlength="8"
                            @input="$refs.editConfirmMirror.value = $event.target.value"
                            class="w-full px-3.5 py-2.5 pr-10 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                        >
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
                            tabindex="-1"
                        >
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                            <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <input type="hidden" name="password_confirmation" x-ref="editConfirmMirror">
                </div>

                <!-- Role Sistem -->
                <div>
                    <label for="edit_role" class="block text-xs font-semibold text-slate-700 mb-1">Role Sistem</label>
                    <div class="relative">
                        <select 
                            id="edit_role" 
                            name="role" 
                            x-model="editUser.role"
                            :disabled="editUser.is_self"
                            class="w-full appearance-none px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition pr-10"
                            :class="{'opacity-60 cursor-not-allowed': editUser.is_self}"
                        >
                            <option value="intern">Peserta Magang (Intern)</option>
                            <option value="pic">PIC Mentor</option>
                            <option value="admin">Admin / HR</option>
                        </select>
                        <div class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <template x-if="editUser.is_self">
                        <input type="hidden" name="role" :value="editUser.role">
                    </template>
                </div>

                <!-- Status Akun (Hanya tampil jika bukan akun sendiri) -->
                <div>
                    <template x-if="!editUser.is_self">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Akun</label>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="is_active" value="1" x-model="editUser.is_active" class="w-3.5 h-3.5 text-[#015987] border-slate-300">
                                    <span class="text-xs text-slate-700 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="is_active" value="0" x-model="editUser.is_active" class="w-3.5 h-3.5 text-[#015987] border-slate-300">
                                    <span class="text-xs text-slate-700 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                </label>
                            </div>
                        </div>
                    </template>
                    <template x-if="editUser.is_self">
                        <input type="hidden" name="is_active" value="1">
                    </template>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-3 pt-2">
                    <button 
                        type="button" 
                        @click="closeModal()" 
                        class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="flex-1 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs shadow-xs hover:shadow-md transition cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL 3: KONFIRMASI KUSTOM NONAKTIFKAN / AKTIFKAN AKUN            -->
    <!-- ================================================================= -->
    <div 
        x-show="activeModal === 'confirm_toggle'" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="closeModal()"></div>

        <!-- Panel Modal -->
        <div 
            @click.stop
            x-show="activeModal === 'confirm_toggle'"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="relative w-full max-w-sm sm:max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 p-7 sm:p-8 text-center"
        >
            <!-- Ikon Status Peringatan (Nonaktifkan: Rose Alert, Aktifkan: Sky/Blue Check) -->
            <div x-show="confirmToggle.isActive" class="mx-auto mb-4 w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-600 shadow-xs">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div x-show="!confirmToggle.isActive" class="mx-auto mb-4 w-16 h-16 rounded-full bg-[#E4F2F8] flex items-center justify-center text-[#015987] shadow-xs">
                <svg class="w-8 h-8 text-[#015987]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <!-- Judul Konfirmasi -->
            <h3 class="text-lg sm:text-xl font-bold text-slate-900" x-text="confirmToggle.isActive ? 'Konfirmasi Nonaktifkan Akun' : 'Konfirmasi Aktifkan Akun'"></h3>

            <!-- Pesan Peringatan Informatif -->
            <p class="text-xs sm:text-sm text-slate-500 mt-2.5 leading-relaxed">
                <span x-show="confirmToggle.isActive">
                    Apakah Anda yakin ingin menonaktifkan akun <strong class="text-slate-800" x-text="confirmToggle.name"></strong>? Pengguna bersangkutan tidak akan dapat masuk ke sistem.
                </span>
                <span x-show="!confirmToggle.isActive">
                    Apakah Anda yakin ingin mengaktifkan kembali akun <strong class="text-slate-800" x-text="confirmToggle.name"></strong>? Pengguna bersangkutan akan dapat masuk kembali ke sistem.
                </span>
            </p>

            <!-- Form Eksekusi Toggle Status -->
            <form method="POST" :action="'/admin/users/' + confirmToggle.id + '/toggle-status'" class="mt-6">
                @csrf
                @method('PATCH')

                <div class="flex items-center gap-3">
                    <button 
                        type="button" 
                        @click="closeModal()" 
                        class="flex-1 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        x-show="confirmToggle.isActive"
                        class="flex-1 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm shadow-sm hover:shadow-md transition cursor-pointer"
                    >
                        Ya, Nonaktifkan
                    </button>
                    <button 
                        type="submit" 
                        x-show="!confirmToggle.isActive"
                        class="flex-1 py-3 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs sm:text-sm shadow-sm hover:shadow-md transition cursor-pointer"
                    >
                        Ya, Aktifkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL 4: LIHAT DETAIL AKUN (Intern, PIC & Admin)                  -->
    <!-- ================================================================= -->
    <div 
        x-show="activeModal === 'detail'" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        style="display: none;"
    >
        <!-- Overlay Background Blur -->
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModal()"></div>

        <!-- Panel Konten Detail -->
        <div 
            x-show="activeModal === 'detail'"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden"
        >
            <!-- Header Gradient -->
            <div class="bg-gradient-to-br from-[#015987] to-[#01435e] px-7 pt-7 pb-10 text-center relative">
                <!-- Tombol Tutup -->
                <button @click="closeModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Avatar -->
                <div class="mx-auto w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm border-2 border-white/30 flex items-center justify-center shadow-lg">
                    <span class="text-2xl font-bold text-white" x-text="detailUser.name ? detailUser.name.charAt(0).toUpperCase() : '?'"></span>
                </div>

                <!-- Nama & Email -->
                <h3 class="text-lg font-bold text-white mt-3" x-text="detailUser.name"></h3>
                <p class="text-xs text-white/70 mt-0.5" x-text="detailUser.email"></p>

                <!-- Badge Role -->
                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold"
                    :class="detailUser.role === 'admin' ? 'bg-amber-400/20 text-amber-100 border border-amber-400/30' : 'bg-sky-400/20 text-sky-100 border border-sky-400/30'"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <template x-if="detailUser.role === 'admin'">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </template>
                        <template x-if="detailUser.role === 'pic'">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </template>
                        <template x-if="detailUser.role === 'intern'">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </template>
                    </svg>
                    <span x-text="detailUser.role_label"></span>
                </div>
            </div>

            <!-- Body Info -->
            <div class="px-7 py-5 -mt-4 bg-white rounded-t-2xl relative z-10 space-y-4">
                <!-- Status Akun -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-xs font-medium text-slate-500">Status Akun</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                        :class="detailUser.is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200'"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="detailUser.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                        <span x-text="detailUser.is_active ? 'AKTIF' : 'NONAKTIF'"></span>
                    </span>
                </div>

                <!-- Tanggal Terdaftar -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-xs font-medium text-slate-500">Terdaftar Sejak</span>
                    <span class="text-xs font-semibold text-slate-700" x-text="detailUser.created_at"></span>
                </div>

                <!-- Info Khusus Peserta (Intern) -->
                <template x-if="detailUser.role === 'intern'">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-sky-50/50 border border-sky-100">
                            <span class="text-xs font-medium text-slate-500">Kode Peserta</span>
                            <span class="text-xs font-semibold text-slate-700" x-text="detailUser.intern_code"></span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-sky-50/50 border border-sky-100 gap-4">
                            <span class="text-xs font-medium text-slate-500 shrink-0">Instansi / Kampus</span>
                            <span class="text-xs font-semibold text-slate-700 text-right" x-text="detailUser.institution"></span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-sky-50/50 border border-sky-100 gap-4">
                            <span class="text-xs font-medium text-slate-500 shrink-0">Program Studi</span>
                            <span class="text-xs font-semibold text-slate-700 text-right" x-text="detailUser.study_program"></span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-sky-50/50 border border-sky-100 gap-4">
                            <span class="text-xs font-medium text-slate-500 shrink-0">Periode Magang</span>
                            <span class="text-xs font-bold text-[#015987] text-right" x-text="detailUser.period"></span>
                        </div>
                    </div>
                </template>

                <!-- Info Khusus PIC -->
                <template x-if="detailUser.role === 'pic'">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-sky-50/50 border border-sky-100">
                            <span class="text-xs font-medium text-slate-500">Peserta Dibimbing</span>
                            <span class="text-xs font-bold text-[#015987]" x-text="detailUser.assigned_interns_count + ' peserta'"></span>
                        </div>
                    </div>
                </template>

                <!-- Info Khusus Admin -->
                <template x-if="detailUser.role === 'admin'">
                    <div class="p-3 rounded-xl bg-amber-50/50 border border-amber-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-700">Administrator Sistem</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Memiliki akses penuh terhadap seluruh fitur pengelolaan sistem.</p>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="px-7 pb-6">
                <button 
                    @click="closeModal()" 
                    class="w-full py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm transition cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function userManagementApp() {
        return {
            activeModal: '{{ old('_modal', '') }}',
            showPassword: false,
            confirmToggle: {
                id: null,
                name: '',
                isActive: false
            },
            detailUser: {
                id: null,
                name: '',
                email: '',
                role: '',
                role_label: '',
                is_active: false,
                created_at: '',
                assigned_interns_count: 0,
                intern_code: '',
                institution: '',
                study_program: '',
                period: '',
                is_self: false
            },
            editUser: {
                id: '{{ old('_modal') === 'edit' ? old('user_id', '') : '' }}',
                name: '{{ old('_modal') === 'edit' ? old('name', '') : '' }}',
                email: '{{ old('_modal') === 'edit' ? old('email', '') : '' }}',
                role: '{{ old('_modal') === 'edit' ? old('role', '') : '' }}',
                is_active: '{{ old('_modal') === 'edit' ? old('is_active', '1') : '1' }}',
                is_self: {{ old('_modal') === 'edit' && old('user_id') == auth()->id() ? 'true' : 'false' }},
            },
            editUrl: '{{ old('_modal') === 'edit' && old('user_id') ? route('admin.users.update', old('user_id')) : '' }}',

            openCreateModal() {
                this.activeModal = 'create';
                this.showPassword = false;
            },

            openDetailModal(data) {
                this.detailUser = {
                    id: data.id,
                    name: data.name,
                    email: data.email,
                    role: data.role,
                    role_label: data.role_label,
                    is_active: data.is_active,
                    created_at: data.created_at,
                    assigned_interns_count: data.assigned_interns_count || 0,
                    intern_code: data.intern_code || '-',
                    institution: data.institution || 'Belum diisi',
                    study_program: data.study_program || 'Belum diisi',
                    period: data.period || 'Belum diatur',
                    is_self: data.is_self
                };
                this.activeModal = 'detail';
            },

            openEditModal(user) {
                this.editUser = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    role: user.role,
                    is_active: user.is_active ? '1' : '0',
                    is_self: user.is_self,
                };
                this.editUrl = `/admin/users/${user.id}`;
                this.showPassword = false;
                this.activeModal = 'edit';
            },

            openConfirmToggleModal(data) {
                this.confirmToggle = {
                    id: data.id,
                    name: data.name,
                    isActive: data.isActive
                };
                this.activeModal = 'confirm_toggle';
            },

            closeModal() {
                this.activeModal = '';
                this.showPassword = false;
                this.confirmToggle = { id: null, name: '', isActive: false };
                this.detailUser = { id: null, name: '', email: '', role: '', role_label: '', is_active: false, created_at: '', assigned_interns_count: 0, intern_code: '', institution: '', study_program: '', period: '', is_self: false };
            },

            init() {
                // Buat fungsi helper global agar tombol di luar scope Alpine tetap dapat memicu modal
                window.openCreateUserModal = () => {
                    this.openCreateModal();
                };

                // Dukungan tutup modal via tombol ESC
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.activeModal) {
                        this.closeModal();
                    }
                });
            }
        }
    }
</script>
@endpush
