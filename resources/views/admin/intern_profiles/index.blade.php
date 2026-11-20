@extends('layouts.app')

@section('title', 'Profil Peserta Magang')

@section('page-title', 'Profil Peserta Magang')
@section('page-subtitle', 'PT WAN Teknologi Internasional – Pengelolaan Data Akademik & Periode Magang')

{{-- Custom Header Right Sesuai Standar WANTRACE --}}
@section('custom-header-right')
    <!-- Widget Tanggal -->
    <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-white px-3.5 py-2.5 rounded-xl border border-slate-200/80 shadow-xs">
        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.intern-profiles.index') }}" class="relative hidden sm:block">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <input 
            type="text" 
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama, kampus..." 
            class="w-64 pl-9 pr-3.5 py-2.5 text-xs bg-white border border-slate-200/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] shadow-xs transition"
        >
        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </form>
@endsection

@section('content')
<div class="space-y-6" x-data="internProfileApp()" x-cloak>

    <!-- Error Validasi (untuk modal) -->
    @if($errors->any() && old('_modal') === 'edit')
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-xs font-semibold text-rose-700 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Terdapat {{ $errors->count() }} kesalahan validasi. Periksa form di bawah.</span>
            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- 1. KARTU STATISTIK RINGKASAN PROFIL MAGANG                        -->
    <!-- ================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Peserta Magang -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between min-h-[136px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold text-slate-700">Total Peserta Magang</span>
                <div class="w-8 h-8 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['total_interns'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">Peserta</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-sky-600 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                    <span>Role intern terdaftar</span>
                </div>
            </div>
        </div>

        <!-- Profil Lengkap -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between min-h-[136px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold text-slate-700">Profil Lengkap</span>
                <div class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['complete_profiles'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">Peserta</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-teal-600 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                    <span>Institusi & periode terisi</span>
                </div>
            </div>
        </div>

        <!-- Belum Lengkap -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between min-h-[136px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold text-slate-700">Perlu Dilengkapi</span>
                <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['incomplete_profiles'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">Peserta</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-amber-600 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Menunggu pengisian Admin</span>
                </div>
            </div>
        </div>

        <!-- Aktif Saat Ini -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between min-h-[136px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-semibold text-slate-700">Aktif Saat Ini</span>
                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5 mt-2">
                    <span class="text-2xl font-bold text-slate-900">{{ $stats['active_interns'] ?? $stats['ongoing_interns'] ?? 0 }}</span>
                    <span class="text-xs text-slate-400 font-medium">Peserta</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-emerald-600 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Akun aktif & sedang magang</span>
                </div>
            </div>
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- 2. TABEL DAFTAR PESERTA MAGANG                                     -->
    <!-- ================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">

        <!-- Header Tabel -->
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Daftar Profil Peserta</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola data akademik dan periode magang setiap peserta.</p>
            </div>
            <!-- Filter Tab -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-semibold">
                <a href="{{ route('admin.intern-profiles.index', ['filter' => 'all', 'search' => request('search')]) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $filter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                    Semua
                </a>
                <a href="{{ route('admin.intern-profiles.index', ['filter' => 'complete', 'search' => request('search')]) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $filter === 'complete' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                    Lengkap
                </a>
                <a href="{{ route('admin.intern-profiles.index', ['filter' => 'incomplete', 'search' => request('search')]) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ $filter === 'incomplete' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                    Belum Lengkap
                </a>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100">
                        <th class="py-3 px-6 font-semibold text-slate-500 uppercase tracking-wider">Peserta Magang</th>
                        <th class="py-3 px-6 font-semibold text-slate-500 uppercase tracking-wider">Institusi</th>
                        <th class="py-3 px-6 font-semibold text-slate-500 uppercase tracking-wider">Status Profil</th>
                        <th class="py-3 px-6 font-semibold text-slate-500 uppercase tracking-wider">Periode Magang</th>
                        <th class="py-3 px-6 font-semibold text-slate-500 uppercase tracking-wider text-center">Durasi</th>
                        <th class="py-3 px-6 font-semibold text-slate-500 uppercase tracking-wider">Status Akun</th>
                        <th class="py-3 px-6 font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($interns as $intern)
                        @php
                            $profile    = $intern->internProfile;
                            $isComplete = $profile && $profile->institution && $profile->internship_start_date;
                            $initials   = strtoupper(substr(trim($intern->name), 0, 1));
                            $second     = strtoupper(substr(strstr(preg_replace('/\s+/', ' ', trim($intern->name)), ' ') ?: ' ', 1, 1));
                            // Siapkan data untuk Alpine modal
                            $startDate = $profile?->internship_start_date?->format('Y-m-d') ?? '';
                            $endDate   = $profile?->internship_end_date?->format('Y-m-d')   ?? '';
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">

                            <!-- Peserta -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-sky-100 text-sky-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ $initials }}{{ $second }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $intern->name }}</p>
                                        <p class="text-slate-400 text-[11px]">{{ $intern->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Institusi -->
                            <td class="py-4 px-6">
                                @if($profile?->institution)
                                    <div class="font-medium text-slate-700">{{ $profile->institution }}</div>
                                    @if($profile->study_program)
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $profile->study_program }}</div>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Belum diisi</span>
                                @endif
                            </td>

                            <!-- Status Profil -->
                            <td class="py-4 px-6">
                                @if($isComplete)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Lengkap
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        Belum Dilengkapi
                                    </span>
                                @endif
                            </td>

                            <!-- Periode Magang -->
                            <td class="py-4 px-6">
                                @if($profile?->internship_start_date)
                                    <div class="font-medium text-slate-700">
                                        {{ $profile->formatted_period }}
                                    </div>
                                    @if($profile->internship_end_date && $profile->internship_end_date->isPast())
                                        <div class="text-[10px] text-slate-400 mt-0.5">Selesai</div>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Tanggal belum diatur</span>
                                @endif
                            </td>

                            <!-- Estimasi Durasi -->
                            <td class="py-4 px-6 text-center">
                                @if($profile?->internship_start_date)
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-100">
                                        {{ $profile->duration_in_months }} Bulan
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <!-- Status Akun -->
                            <td class="py-4 px-6">
                                @if($intern->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        AKTIF
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                        NONAKTIF
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-6 text-right">
                                <button 
                                    @click="openEditModal({
                                        id: {{ $intern->id }},
                                        name: '{{ addslashes($intern->name) }}',
                                        email: '{{ addslashes($intern->email) }}',
                                        isActive: {{ $intern->is_active ? 'true' : 'false' }},
                                        institution: '{{ addslashes($profile?->institution ?? '') }}',
                                        study_program: '{{ addslashes($profile?->study_program ?? '') }}',
                                        start_date: '{{ $startDate }}',
                                        end_date: '{{ $endDate }}'
                                    })"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg border {{ $isComplete ? 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' : 'border-[#015987] bg-[#015987] text-white hover:bg-[#014467]' }} text-xs font-semibold transition shadow-2xs"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>{{ $isComplete ? 'Edit Profil' : 'Lengkapi Profil' }}</span>
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <p class="text-sm font-semibold text-slate-500">Tidak ada peserta magang yang ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba ubah filter atau kata kunci pencarian Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Paginasi -->
        @if($interns->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ $interns->firstItem() }} sampai {{ $interns->lastItem() }} dari {{ $interns->total() }} peserta
                </p>
                <div>
                    {{ $interns->links() }}
                </div>
            </div>
        @endif

    </div>

    <!-- ================================================================= -->
    <!-- MODAL: EDIT / LENGKAPI PROFIL PESERTA                             -->
    <!-- ================================================================= -->
    <div
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeModal()"></div>

        <!-- Modal Panel -->
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-200/80 w-full max-w-lg max-h-[90vh] overflow-y-auto"
            @click.stop
        >
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900" x-text="editIntern.isComplete ? 'Edit Profil Peserta' : 'Lengkapi Profil Peserta'"></h3>
                    <p class="text-xs text-[#015987] font-medium mt-0.5">Isi data institusi dan periode magang peserta.</p>
                </div>
                <button @click="closeModal()" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Kartu Info Peserta -->
            <div class="px-6 pt-5">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-sky-100 text-sky-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                            <span x-text="editIntern.initials"></span>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 leading-tight" x-text="editIntern.name"></h4>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="editIntern.email"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-sky-100 text-sky-700">INTERN</span>
                        <p class="text-[10px] font-semibold mt-1" :class="editIntern.isActive ? 'text-emerald-600' : 'text-slate-400'" x-text="editIntern.isActive ? '● Aktif' : '○ Nonaktif'"></p>
                    </div>
                </div>
            </div>

            <!-- Error Validasi di dalam modal -->
            @if($errors->any() && old('_modal') === 'edit')
                <div class="mx-6 mt-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200/80 text-xs text-rose-700">
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form method="POST" :action="'/admin/intern-profiles/' + editIntern.id" class="px-6 pb-6 pt-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">
                <input type="hidden" name="user_id" :value="editIntern.id">
                <input type="hidden" name="name" :value="editIntern.name">
                <input type="hidden" name="email" :value="editIntern.email">
                <input type="hidden" name="is_active" :value="editIntern.isActive ? '1' : '0'">

                <!-- Asal Institusi / Universitas -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Asal Institusi / Universitas</label>
                    <input 
                        type="text" 
                        name="institution" 
                        :value="editIntern.institution"
                        placeholder="Contoh: Politeknik Negeri Padang"
                        maxlength="150"
                        class="w-full px-4 py-3 text-sm bg-slate-50 border {{ $errors->has('institution') ? 'border-rose-300' : 'border-slate-200' }} rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                    @error('institution')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Program Studi / Jurusan -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Program Studi / Jurusan</label>
                    <input 
                        type="text" 
                        name="study_program" 
                        :value="editIntern.study_program"
                        placeholder="Contoh: Teknologi Rekayasa Perangkat Lunak"
                        maxlength="150"
                        class="w-full px-4 py-3 text-sm bg-slate-50 border {{ $errors->has('study_program') ? 'border-rose-300' : 'border-slate-200' }} rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                    >
                    @error('study_program')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Mulai & Selesai -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="modal_start_date"
                            name="internship_start_date" 
                            :value="editIntern.start_date"
                            @change="calculateDuration()"
                            class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border {{ $errors->has('internship_start_date') ? 'border-rose-300' : 'border-slate-200' }} rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                        >
                        @error('internship_start_date')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Tanggal Selesai <span class="text-xs font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input 
                            type="date" 
                            id="modal_end_date"
                            name="internship_end_date" 
                            :value="editIntern.end_date"
                            @change="calculateDuration()"
                            class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border {{ $errors->has('internship_end_date') ? 'border-rose-300' : 'border-slate-200' }} rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition"
                        >
                        @error('internship_end_date')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Estimasi Durasi -->
                <div class="px-4 py-3 rounded-xl bg-sky-50/70 border border-sky-100 flex items-center justify-between text-xs">
                    <span class="font-medium text-slate-600 flex items-center gap-2">
                        <svg class="w-4 h-4 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Estimasi Durasi Magang:</span>
                    </span>
                    <span id="modalDurationText" class="font-bold text-[#015987]">Tentukan tanggal magang</span>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-3 pt-2">
                    <button 
                        type="button"
                        @click="closeModal()"
                        class="flex-1 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm text-center transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="flex-1 py-3 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-sm shadow-xs hover:shadow-md transition"
                    >
                        Simpan Data Profil
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function internProfileApp() {
    return {
        showModal: false,
        editIntern: {
            id: null,
            name: '',
            email: '',
            isActive: false,
            isComplete: false,
            initials: '',
            institution: '',
            study_program: '',
            start_date: '',
            end_date: ''
        },

        openEditModal(data) {
            const name = data.name || '';
            const parts = name.trim().replace(/\s+/g, ' ').split(' ');
            const initials = (parts[0] ? parts[0][0].toUpperCase() : '') + 
                             (parts[1] ? parts[1][0].toUpperCase() : '');

            this.editIntern = {
                id: data.id,
                name: name,
                email: data.email || '',
                isActive: data.isActive,
                isComplete: !!(data.institution && data.start_date),
                initials: initials,
                institution: data.institution || '',
                study_program: data.study_program || '',
                start_date: data.start_date || '',
                end_date: data.end_date || ''
            };
            this.showModal = true;
            document.body.style.overflow = 'hidden';

            // Hitung durasi setelah modal terbuka (sedikit delay)
            setTimeout(() => {
                this.calculateDuration();
            }, 100);
        },

        closeModal() {
            this.showModal = false;
            document.body.style.overflow = '';
        },

        calculateDuration() {
            const startInput = document.getElementById('modal_start_date');
            const endInput   = document.getElementById('modal_end_date');
            const durationEl = document.getElementById('modalDurationText');

            if (!startInput || !durationEl) return;

            const startVal = startInput.value;
            const endVal   = endInput ? endInput.value : '';

            if (!startVal) {
                durationEl.textContent = 'Tentukan tanggal magang';
                durationEl.className = 'font-bold text-[#015987]';
                return;
            }

            const startDate = new Date(startVal);
            const endDate   = endVal ? new Date(endVal) : new Date();

            if (endVal && endDate < startDate) {
                durationEl.textContent = 'Tanggal selesai harus setelah tanggal mulai';
                durationEl.className = 'font-bold text-rose-500';
                return;
            }

            durationEl.className = 'font-bold text-[#015987]';

            let months = (endDate.getFullYear() - startDate.getFullYear()) * 12;
            months    -= startDate.getMonth();
            months    += endDate.getMonth();
            if (endDate.getDate() < startDate.getDate()) months--;

            const finalMonths = Math.max(1, Math.round(months <= 0 ? 1 : months));
            const days        = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24));
            durationEl.textContent = finalMonths + ' Bulan (' + days + ' Hari)';
        },

        init() {
            // Auto-reopen modal jika ada error validasi dari server
            const modalType = '{{ old('_modal') }}';
            if (modalType === 'edit') {
                this.openEditModal({
                    id: '{{ old('user_id') }}',
                    name: '{{ addslashes(old('name', '')) }}',
                    email: '{{ addslashes(old('email', '')) }}',
                    isActive: {{ old('is_active') === '1' ? 'true' : 'false' }},
                    institution: '{{ addslashes(old('institution', '')) }}',
                    study_program: '{{ addslashes(old('study_program', '')) }}',
                    start_date: '{{ old('internship_start_date', '') }}',
                    end_date:   '{{ old('internship_end_date', '') }}'
                });
            }
            @if(isset($editUser) && $editUser && old('_modal') !== 'edit')
                @php
                    $p = $editUser->internProfile;
                @endphp
                this.openEditModal({
                    id: {{ $editUser->id }},
                    name: '{{ addslashes($editUser->name) }}',
                    email: '{{ addslashes($editUser->email) }}',
                    isActive: {{ $editUser->is_active ? 'true' : 'false' }},
                    institution: '{{ addslashes($p?->institution ?? '') }}',
                    study_program: '{{ addslashes($p?->study_program ?? '') }}',
                    start_date: '{{ $p?->internship_start_date?->format('Y-m-d') ?? '' }}',
                    end_date:   '{{ $p?->internship_end_date?->format('Y-m-d') ?? '' }}'
                });
            @endif

            // Keyboard ESC untuk tutup modal
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.showModal) {
                    this.closeModal();
                }
            });
        }
    };
}
</script>
@endpush
