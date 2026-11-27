@extends('layouts.app')

@section('title', 'Dashboard Monitoring Magang - Admin')
@section('page-title', 'Dashboard Monitoring Magang')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Pusat Kendali & Evaluasi Kegiatan Magang')
@section('search-placeholder', 'Cari peserta...')

@section('content')
<div class="space-y-8">

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Total Peserta Aktif -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Total Peserta Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $adminSummary['total_active_interns'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Peserta</span>
                </div>
                <div class="flex items-center justify-between mt-1.5 text-[11px]">
                    <span class="font-semibold text-sky-600 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                        Aktif Terdaftar
                    </span>
                    <a href="{{ route('admin.users.index', ['role' => 'intern']) }}" class="text-[#015987] font-semibold hover:underline">
                        Lihat Data &rsaquo;
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 2: Kehadiran Hari Ini -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Kehadiran Hari Ini</span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $adminSummary['total_present'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">dari {{ $adminSummary['total_active_interns'] }} Peserta</span>
                </div>
                <p class="text-[11px] font-semibold text-teal-600 mt-1.5 flex items-center gap-1 truncate">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    {{ $adminSummary['on_time_count'] }} Tepat Waktu &bull; {{ $adminSummary['late_count'] }} Terlambat
                </p>
            </div>
        </div>

        <!-- Card 3: Tugas Menunggu Review -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Tugas Menunggu Review</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $taskSummary['awaiting_review'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <div class="flex items-center justify-between mt-1.5 text-[11px]">
                    <span class="font-semibold text-indigo-600 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        {{ $taskSummary['in_progress'] }} Sedang Dikerjakan
                    </span>
                    <a href="{{ route('admin.tasks.index') }}" class="text-[#015987] font-semibold hover:underline">
                        Daftar &rsaquo;
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 4: Sinyal Peringatan (EWS) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Sinyal Peringatan (EWS)</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold {{ $ewsSummary['total_warning_count'] > 0 ? 'text-rose-600' : 'text-[#015987]' }}">{{ $ewsSummary['total_warning_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Sinyal</span>
                </div>
                <p class="text-[11px] font-semibold {{ $ewsSummary['total_warning_count'] > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $ewsSummary['total_warning_count'] > 0 ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                    @if($ewsSummary['total_warning_count'] > 0)
                        {{ $ewsSummary['unsubmitted_attendance_count'] }} Presensi &bull; {{ $ewsSummary['missing_logbook_count'] }} Logbook &bull; {{ $ewsSummary['overdue_tasks_count'] }} Tugas
                    @else
                        Kondisi Seluruh Peserta Tertib
                    @endif
                </p>
            </div>
        </div>

    </div>

    <!-- Banner Ringkasan Akun & Operasional Sistem -->
    <div class="bg-gradient-to-r from-sky-50 to-indigo-50/50 border border-sky-100 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#015987] text-white flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-tight">
                    Status Operasional Kantor: <span class="text-[#015987]">{{ $adminSummary['day_status']['label'] ?? 'Hari Kerja' }}</span>
                </h4>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ $adminSummary['day_status']['reason'] ?? 'Operasional PT WAN Teknologi Internasional' }} &bull; Jam Kerja 08.00 - 17.00 WIB
                </p>
            </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ $stats['total_active'] ?? 0 }} Akun Aktif</span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                <span>{{ $stats['pic_active'] ?? $stats['total_active_pics'] ?? 0 }} PIC Mentor Aktif</span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                <span>{{ $stats['inactive_users'] ?? 0 }} Nonaktif/Alumni</span>
            </span>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. GRID 2 KOLOM (PRESENSI, IZIN SAKIT, TUGAS, & SINYAL EWS)    -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- KOLOM KIRI (LEBAR 7/12): PRESENSI HARIAN & LOGBOOK TERBARU -->
        <div class="lg:col-span-7 space-y-8">

            <!-- KARTU 1: TABEL PRESENSI HARIAN PESERTA -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <!-- Header Kartu -->
                <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-bold text-[#015987]">Presensi Harian Peserta</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $adminSummary['total_present'] }} Hadir
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                {{ $adminSummary['on_time_count'] }} Tepat Waktu
                            </span>
                            @if(($adminSummary['late_count'] ?? 0) > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $adminSummary['late_count'] }} Terlambat
                                </span>
                            @endif
                            @if(! empty($search))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-[#015987] border border-sky-200">
                                    Filter: "{{ $search }}"
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Tanggal: {{ \Carbon\Carbon::parse($adminSummary['date'])->translatedFormat('l, d F Y') }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(! empty($search))
                            <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1.5 text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition font-medium">
                                Reset Cari
                            </a>
                        @endif
                        <a href="{{ route('admin.attendance.index', ['date' => $adminSummary['date']]) }}" class="text-xs font-semibold text-[#015987] hover:underline flex items-center gap-1">
                            Lihat Semua Presensi &rsaquo;
                        </a>
                    </div>
                </div>

                @if(! ($adminSummary['day_status']['is_working_day'] ?? true))
                    <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-2 text-xs text-slate-600">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Hari ini adalah <strong>{{ $adminSummary['day_status']['label'] ?? 'Hari Libur' }}</strong>. Tidak ada kewajiban presensi reguler.</span>
                    </div>
                @endif

                <!-- Form Cari Cepat di Tabel -->
                <div class="px-6 py-3 bg-slate-50/50 border-b border-slate-100">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ $search ?? '' }}" 
                                placeholder="Cari nama atau email peserta di tabel..." 
                                class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987]"
                            >
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <button type="submit" class="px-3.5 py-1.5 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-2xs transition">
                            Cari
                        </button>
                    </form>
                </div>

                <!-- Tabel Data -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-4 px-6">Nama Peserta</th>
                                <th class="py-4 px-5">Jam Masuk</th>
                                <th class="py-4 px-5 text-center">Foto Bukti</th>
                                <th class="py-4 px-5">Status</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($adminSummary['attendances'] as $att)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-sky-100 text-[#015987] font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                                {{ strtoupper(substr($att->intern->name ?? 'P', 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-[#015987] text-xs leading-none">{{ $att->intern->name ?? '-' }}</p>
                                                <p class="text-[10px] text-slate-400 mt-1">{{ $att->intern->email ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 font-semibold text-slate-700">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 {{ $att->isLate() ? 'text-rose-500' : 'text-teal-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ \Carbon\Carbon::parse($att->checked_in_at)->timezone(config('wantrace.timezone', 'Asia/Jakarta'))->format('H:i') }} WIB
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        @if($att->photo_media_id)
                                            <a href="{{ route('media.private.show', $att->photo_media_id) }}" target="_blank" class="block w-8 h-8 rounded-lg bg-slate-200 mx-auto overflow-hidden border border-slate-200 hover:ring-2 hover:ring-sky-500 transition" title="Lihat foto selfie">
                                                <img src="{{ route('media.private.show', $att->photo_media_id) }}" alt="Foto" class="w-full h-full object-cover">
                                            </a>
                                        @else
                                            <span class="text-slate-300 text-[10px]">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5">
                                        @if($att->isRejected())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1 h-1 rounded-full bg-rose-600"></span>
                                                Ditolak HR
                                            </span>
                                        @elseif($att->isOnTime())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-600 border border-teal-200">
                                                <span class="w-1 h-1 rounded-full bg-teal-500"></span>
                                                Tepat Waktu
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                                                <span class="w-1 h-1 rounded-full bg-rose-500"></span>
                                                Terlambat
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <a href="{{ route('admin.attendance.index', ['date' => $adminSummary['date']]) }}" class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition inline-block">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-xs text-slate-400">
                                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Belum ada data presensi yang sesuai kriteria hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer Tabel Dinamis -->
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <span>Menampilkan {{ $adminSummary['attendances']->count() }} dari {{ $adminSummary['total_present'] }} kehadiran hari ini</span>
                    <span class="font-semibold text-sky-600">
                        {{ $adminSummary['total_active_interns'] > 0 ? round(($adminSummary['total_present'] / $adminSummary['total_active_interns']) * 100, 1) : 0 }}% tingkat kehadiran
                    </span>
                </div>
            </div>

            <!-- KARTU 2: AKTIVITAS LOGBOOK MANUAL TERBARU -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-[#015987]">Aktivitas Logbook Manual Terbaru</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Catatan kegiatan harian mandiri yang dikirimkan peserta</p>
                    </div>
                    <a href="{{ route('admin.logbooks.index') }}" class="text-xs font-semibold text-[#015987] hover:underline flex items-center gap-1">
                        Lihat Semua Logbook &rsaquo;
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($latestLogbooks as $logbook)
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/60 transition">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-[#015987] truncate">{{ $logbook->intern->name ?? 'Peserta' }}</span>
                                    <span class="text-[10px] text-slate-400">&bull; {{ \Carbon\Carbon::parse($logbook->log_date)->translatedFormat('d M Y') }}</span>
                                    @if($logbook->status?->value === 'approved')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Disetujui</span>
                                    @elseif($logbook->status?->value === 'revision_requested')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Perlu Revisi</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">Menunggu Review</span>
                                    @endif
                                </div>
                                <h4 class="text-xs font-semibold text-slate-800 leading-snug">{{ $logbook->title }}</h4>
                                <p class="text-[11px] text-slate-500 line-clamp-1">{{ Str::limit($logbook->activity_description, 100) }}</p>
                            </div>
                            <div class="flex-shrink-0 self-end sm:self-center">
                                <a href="{{ route('admin.logbooks.show', $logbook) }}" class="px-3 py-1.5 text-[11px] font-medium text-[#015987] border border-sky-200 bg-sky-50 hover:bg-sky-100 rounded-lg transition inline-block">
                                    Detail Logbook
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-400">
                            Belum ada entri logbook manual yang tercatat di sistem.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (LEBAR 5/12): STATUS MONITORING IZIN & SINYAL EWS -->
        <div class="lg:col-span-5 space-y-8">
            
            <!-- KARTU 1: PENGAJUAN IZIN & SAKIT PENDING -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Pengajuan Izin &amp; Sakit</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Permintaan terbaru yang memerlukan keputusan HR</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-semibold {{ $adminSummary['pending_leaves_count'] > 0 ? 'text-rose-500 bg-rose-50' : 'text-slate-500 bg-slate-100' }} px-2.5 py-0.5 rounded-full">
                            {{ $adminSummary['pending_leaves_count'] }} Menunggu
                        </span>
                        <a href="{{ route('admin.leave.index') }}" class="text-xs text-[#015987] font-semibold hover:underline">
                            Semua &rsaquo;
                        </a>
                    </div>
                </div>

                @forelse($adminSummary['pending_leaves_list'] as $leave)
                    <div class="p-5 rounded-xl border border-slate-100 bg-slate-50/50 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full {{ $leave->isSick() ? 'bg-rose-100 text-rose-600' : 'bg-sky-100 text-[#015987]' }} font-bold text-[10px] flex items-center justify-center">
                                    {{ strtoupper(substr($leave->intern->name ?? 'P', 0, 2)) }}
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-[#015987]">{{ $leave->intern->name ?? 'Peserta' }}</span>
                                    <span class="block text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }} ({{ $leave->duration_days }} hari)</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold {{ $leave->isSick() ? 'text-rose-500 bg-rose-50' : 'text-sky-600 bg-sky-50' }} px-2 py-0.5 rounded">
                                {{ $leave->isSick() ? 'Sakit' : 'Izin' }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-600">
                            {{ $leave->reason }}
                        </p>

                        @if($leave->evidence)
                            <a href="{{ route('media.private.show', $leave->evidence_media_id) }}" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 bg-white rounded-xl border border-slate-200/80 text-xs text-slate-600 hover:border-slate-300 transition group">
                                <span class="flex items-center gap-2 truncate text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#015987]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="truncate">{{ $leave->evidence->original_name }}</span>
                                </span>
                                <span class="text-slate-400 text-xs group-hover:text-slate-600">&rsaquo;</span>
                            </a>
                        @endif

                        <div class="grid grid-cols-2 gap-2.5 pt-1">
                            <form method="POST" action="{{ route('admin.leave.review', $leave) }}" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pengajuan izin ini?')">
                                @csrf
                                <input type="hidden" name="decision" value="approved">
                                <button type="submit" class="w-full py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                                    Setujui
                                </button>
                            </form>
                            <button type="button" onclick="openRejectModal({{ $leave->id }}, '{{ addslashes($leave->intern->name ?? 'Peserta') }}')" class="w-full py-2 bg-white hover:bg-rose-50 text-rose-500 border border-rose-200 text-xs font-semibold rounded-xl transition">
                                Tolak
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-xs text-slate-400 space-y-2">
                        <svg class="w-8 h-8 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>Tidak ada permohonan izin/sakit yang menunggu verifikasi saat ini.</p>
                    </div>
                @endforelse
            </div>

            <!-- KARTU 2: SINYAL PERINGATAN DINI (EWS 3 INDIKATOR RESMI) -->
            <div id="ews-section" class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Peringatan Dini (EWS)</h3>
                            <p class="text-[11px] text-slate-400">Presensi, Logbook, &amp; Tugas Terlewat</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $ewsSummary['total_warning_count'] > 0 ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-teal-50 text-teal-700 border border-teal-200' }}">
                        {{ $ewsSummary['total_warning_count'] }} Kasus
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($ewsSummary['warnings_list'] as $warning)
                        <div class="p-3.5 rounded-xl border {{ $warning['level'] === 'danger' ? 'border-rose-200 bg-rose-50/40' : 'border-amber-200 bg-amber-50/40' }} space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $warning['level'] === 'danger' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $warning['badge'] }}
                                </span>
                                <span class="text-[10px] font-semibold text-slate-500">
                                    {{ $warning['intern']->name ?? 'Peserta' }}
                                </span>
                            </div>
                            <h5 class="text-xs font-bold text-slate-800 leading-snug">{{ $warning['title'] }}</h5>
                            <p class="text-[11px] text-slate-600 leading-relaxed">{{ $warning['detail'] }}</p>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400 space-y-2">
                            <svg class="w-8 h-8 mx-auto text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-semibold text-emerald-600">Tidak ada sinyal peringatan EWS aktif</p>
                            <p class="text-[11px] text-slate-400">Seluruh peserta mematuhi presensi, logbook, dan deadline tugas.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

@push('modals')
<!-- Modal Tolak Permohonan Izin -->
<div id="rejectModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto hidden">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h4 class="text-sm font-bold text-slate-900">Tolak Permohonan Izin</h4>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="rejectForm" method="POST" action="" class="space-y-4">
            @csrf
            <input type="hidden" name="decision" value="rejected">
            
            <p class="text-xs text-slate-600">
                Anda akan menolak permohonan izin dari <strong id="rejectInternName" class="text-slate-900"></strong>. Sesuai ketentuan, alasan penolakan wajib dicatat.
            </p>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alasan Penolakan <span class="text-rose-500">*</span></label>
                <textarea name="review_notes" rows="3" required placeholder="Tuliskan catatan alasan penolakan permohonan..." class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-medium hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                    Konfirmasi Tolak
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openRejectModal(id, name) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const nameSpan = document.getElementById('rejectInternName');
        
        form.action = `/admin/izin/${id}/review`;
        nameSpan.textContent = name;
        modal.classList.remove('hidden');
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.add('hidden');
    }
</script>
@endpush
@endsection
