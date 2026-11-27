@extends('layouts.app')

@section('title', 'Dashboard Monitoring Magang - Peserta')
@section('page-title', 'Dashboard Monitoring Magang')
@section('page-subtitle', 'PT WAN Teknologi Internasional')
@section('search-placeholder', 'Cari tugas / aktivitas...')

@section('content')
<div class="space-y-8">

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN PESERTA MAGANG -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Presensi Hari Ini -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Presensi Hari Ini</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                @if($dailySummary['attendance_today'] && $dailySummary['attendance_today']->isRejected())
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-rose-600">Ditolak HR</span>
                    </div>
                    <p class="text-[11px] font-semibold text-rose-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                        Perlu Foto Ulang • Batas: Hari ini
                    </p>
                @elseif($dailySummary['attendance_today'])
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">Hadir</span>
                        <span class="text-xs text-slate-500 font-medium">{{ \Carbon\Carbon::parse($dailySummary['attendance_today']->checked_in_at)->timezone(config('wantrace.timezone', 'Asia/Jakarta'))->format('H:i') }} WIB</span>
                    </div>
                    @if($dailySummary['attendance_today']->isOnTime())
                        <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Tepat Waktu
                        </p>
                    @else
                        <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Terlambat
                        </p>
                    @endif
                @elseif($dailySummary['approved_leave_today'])
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-amber-600">{{ $dailySummary['approved_leave_today']->isSick() ? 'Sakit' : 'Izin' }}</span>
                        <span class="text-xs text-slate-500 font-medium">Disetujui</span>
                    </div>
                    <p class="text-[11px] font-semibold text-amber-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Izin Sah Terverifikasi
                    </p>
                @elseif(! ($dailySummary['day_status']['is_working_day'] ?? true))
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-700">Libur</span>
                        <span class="text-xs text-slate-500 font-medium">Operasional</span>
                    </div>
                    <p class="text-[11px] font-semibold text-slate-500 mt-1.5 truncate">
                        {{ $dailySummary['day_status']['label'] ?? 'Hari Libur' }}
                    </p>
                @else
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-extrabold text-rose-600">Belum</span>
                        <span class="text-xs text-slate-500 font-medium">Presensi</span>
                    </div>
                    <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                        Batas Tepat Waktu 08:05 WIB
                    </p>
                @endif
            </div>
        </div>

        <!-- Card 2: Pengajuan Izin / Sakit -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Pengajuan Izin / Sakit</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $dailySummary['pending_leaves_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Menunggu</span>
                </div>
                @if($dailySummary['pending_leaves_count'] > 0)
                    <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Menunggu Review HR
                    </p>
                @else
                    <p class="text-[11px] font-semibold text-slate-400 mt-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tidak Ada Permohonan Aktif
                    </p>
                @endif
            </div>
        </div>

        <!-- Card 3: Tugas Menunggu Selesai -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Tugas Menunggu Selesai</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $internDashboard['active_tasks_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas Aktif</span>
                </div>
                @if ($internDashboard['overdue_tasks_count'] > 0)
                    <p class="text-[11px] font-semibold text-rose-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                        {{ $internDashboard['overdue_tasks_count'] }} Melewati Deadline (Overdue)
                    </p>
                @elseif ($internDashboard['approaching_tasks_count'] > 0)
                    <p class="text-[11px] font-semibold text-amber-600 mt-1.5 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $internDashboard['approaching_tasks_count'] }} Mendekati Deadline (&le; 24 jam)
                    </p>
                @elseif ($internDashboard['active_tasks_count'] > 0)
                    <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                        Dalam Tenggat Waktu
                    </p>
                @else
                    <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Semua Tugas Selesai
                    </p>
                @endif
            </div>
        </div>

        <!-- Card 4: Logbook Harian -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Logbook Harian</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $internDashboard['today_logbooks_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Catatan Hari Ini</span>
                </div>
                @if ($internDashboard['revision_logbooks_count'] > 0)
                    <p class="text-[11px] font-semibold text-rose-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        {{ $internDashboard['revision_logbooks_count'] }} Logbook Perlu Revisi
                    </p>
                @elseif ($internDashboard['today_logbooks_count'] > 0)
                    <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Sudah Tercatat Hari Ini
                    </p>
                @else
                    <a href="{{ route('intern.logbooks.index') }}" class="text-[11px] font-semibold text-amber-600 hover:underline mt-1.5 inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Belum Diisi Hari Ini &rarr;
                    </a>
                @endif
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. GRID 2 KOLOM KONTEN UTAMA (KIRI 7/12 & KANAN 5/12) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ========================================================== -->
        <!-- KOLOM KIRI (LEBAR 7/12): BANNER PRESENSI & TABEL TUGAS -->
        <!-- ========================================================== -->
        <div class="lg:col-span-7 space-y-8">

            <!-- BANNER 1: STATUS PRESENSI HARIAN PESERTA -->
            @if($dailySummary['attendance_today'] && $dailySummary['attendance_today']->isRejected())
                <div class="bg-rose-700 text-white p-6 sm:p-7 rounded-2xl shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">
                                Foto Presensi Masuk Ditolak HR
                            </h3>
                        </div>
                        <p class="text-xs text-rose-100">
                            Alasan: "{{ $dailySummary['attendance_today']->notes }}"
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/10 text-white text-xs font-medium mt-1">
                            <span>Waktu Awal: {{ \Carbon\Carbon::parse($dailySummary['attendance_today']->checked_in_at)->timezone(config('wantrace.timezone', 'Asia/Jakarta'))->format('H:i:s') }} WIB (Status {{ $dailySummary['attendance_today']->isOnTime() ? 'Tepat Waktu' : 'Terlambat' }} tetap dipertahankan)</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('intern.attendance.index') }}" class="px-5 py-2.5 bg-white text-rose-700 hover:bg-rose-50 text-xs font-bold rounded-xl transition shadow-xs inline-block">
                            Buka Kamera &amp; Foto Ulang
                        </a>
                    </div>
                </div>
            @elseif($dailySummary['approved_leave_today'])
                <div class="bg-[#78350f] text-white p-6 sm:p-7 rounded-2xl shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">
                                Izin Resmi Disetujui Hari Ini
                            </h3>
                        </div>
                        <p class="text-xs text-amber-100">
                            Anda tercatat {{ $dailySummary['approved_leave_today']->isSick() ? 'Sakit' : 'Izin' }} ({{ Str::limit($dailySummary['approved_leave_today']->reason, 75) }}). Ketidakhadiran sah dan tidak tercatat alpa.
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/10 text-white text-xs font-medium mt-1">
                            <span>Periode: {{ \Carbon\Carbon::parse($dailySummary['approved_leave_today']->start_date)->translatedFormat('d M') }} s/d {{ \Carbon\Carbon::parse($dailySummary['approved_leave_today']->end_date)->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('intern.leave.index') }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-xl transition shadow-xs inline-block">
                            Detail Izin
                        </a>
                    </div>
                </div>
            @elseif(! ($dailySummary['day_status']['is_working_day'] ?? true))
                <div class="bg-slate-800 text-white p-6 sm:p-7 rounded-2xl shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">
                                Hari Ini Libur Operasional
                            </h3>
                        </div>
                        <p class="text-xs text-slate-300">
                            {{ $dailySummary['day_status']['reason'] ?? ($dailySummary['day_status']['label'] ?? 'Hari Libur') }}{{ !empty($dailySummary['day_status']['description']) ? ' — ' . $dailySummary['day_status']['description'] : '' }}
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/10 text-slate-200 text-xs font-medium mt-1">
                            <span>Tidak ada kewajiban presensi masuk hari ini</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('intern.attendance.index') }}" class="px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold rounded-xl transition shadow-xs inline-block">
                            Lihat Riwayat
                        </a>
                    </div>
                </div>
            @elseif($dailySummary['attendance_today'])
                <div class="bg-[#014467] text-white p-6 sm:p-7 rounded-2xl shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">
                                Presensi Masuk Berhasil Tercatat
                            </h3>
                        </div>
                        <p class="text-xs text-sky-100">
                            Selfie valid hari ini • Status: <span class="font-bold {{ $dailySummary['attendance_today']->isOnTime() ? 'text-emerald-300' : 'text-amber-300' }}">{{ $dailySummary['attendance_today']->isOnTime() ? 'Tepat Waktu' : 'Terlambat' }}</span>.
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/10 text-white text-xs font-medium mt-1">
                            <svg class="w-3.5 h-3.5 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Waktu Resmi: {{ \Carbon\Carbon::parse($dailySummary['attendance_today']->checked_in_at)->timezone(config('wantrace.timezone', 'Asia/Jakarta'))->format('H:i:s') }} WIB</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('intern.attendance.index') }}" class="px-5 py-2.5 bg-[#0284c7] hover:bg-sky-500 text-white text-xs font-semibold rounded-xl transition shadow-xs inline-block">
                            Lihat Presensi
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-[#0f2d42] border border-sky-800 text-white p-6 sm:p-7 rounded-2xl shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">
                                Belum Melakukan Presensi Masuk
                            </h3>
                        </div>
                        <p class="text-xs text-sky-100">
                            Hari kerja resmi PT WAN. Presensi dibuka dengan batas toleransi tepat waktu pukul 08:05 WIB.
                        </p>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-rose-500/20 text-rose-200 border border-rose-400/30 text-xs font-medium mt-1">
                            <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                            <span>Segera ambil foto selfie untuk absensi hari ini</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('intern.attendance.index') }}" class="px-5 py-2.5 bg-[#015987] hover:bg-sky-600 text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Presensi Sekarang</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- KARTU 2: TUGAS SAYA & TENGGAT TERDEKAT -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <!-- Header Kartu -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-[#015987]">Tugas Saya &amp; Tenggat Terdekat</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Daftar tugas magang aktif dan instruksi penyelesaian PIC</p>
                    </div>
                    <a href="{{ route('intern.tasks.index') }}" class="text-xs font-semibold text-[#015987] hover:underline flex items-center gap-1 shrink-0">
                        Buka Semua Tugas &rsaquo;
                    </a>
                </div>

                @if ($internDashboard['upcoming_tasks']->isEmpty())
                    <div class="p-8 text-center space-y-2">
                        <p class="text-xs font-bold text-slate-600">Tidak ada tugas aktif saat ini</p>
                        <p class="text-[11px] text-slate-400">Tugas baru dari PIC Pembimbing akan otomatis muncul di sini.</p>
                    </div>
                @else
                    <!-- Tabel Data Tugas Asli -->
                    @php
                        $nowJakarta = \Illuminate\Support\Carbon::now(\App\Models\Task::TIMEZONE);
                        $soonCutoff = $nowJakarta->copy()->addHours(24);
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="py-4 px-6">Judul Tugas</th>
                                    <th class="py-4 px-5">PIC Pembimbing</th>
                                    <th class="py-4 px-5">Tenggat Waktu</th>
                                    <th class="py-4 px-5">Status</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach ($internDashboard['upcoming_tasks'] as $task)
                                    @php
                                        $isOverdue = $task->isOverdue($nowJakarta);
                                        $isApproaching = ! $isOverdue && $task->deadline_at && $task->deadline_at->lte($soonCutoff);

                                        if ($isOverdue) {
                                            $badgeText = 'Overdue';
                                            $badgeClass = 'bg-rose-50 text-rose-600 border-rose-200';
                                        } elseif ($task->status->value === 'revision_requested') {
                                            $badgeText = 'Perlu Revisi';
                                            $badgeClass = 'bg-rose-50 text-rose-600 border-rose-200';
                                        } elseif ($isApproaching) {
                                            $badgeText = 'Mendekati Deadline';
                                            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                        } else {
                                            $badgeText = $task->status->label();
                                            $badgeClass = match ($task->status->value) {
                                                'submitted', 'resubmitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                                            };
                                        }

                                        $actionLabel = match ($task->status->value) {
                                            'revision_requested' => 'Kumpulkan Ulang',
                                            'assigned', 'in_progress' => 'Kerjakan',
                                            default => 'Detail',
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-4 px-6">
                                            <div>
                                                <p class="font-bold text-[#015987] text-xs leading-snug">{{ $task->title }}</p>
                                                @if (filled($task->description))
                                                    <p class="text-[10px] text-slate-400 mt-0.5 line-clamp-1">{{ \Illuminate\Support\Str::limit($task->description, 65) }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-5 font-medium text-slate-700 whitespace-nowrap">
                                            {{ $task->pic?->name ?? 'PIC' }}
                                        </td>
                                        <td class="py-4 px-5 whitespace-nowrap font-semibold {{ $isOverdue ? 'text-rose-600' : ($isApproaching ? 'text-amber-600' : 'text-slate-600') }}">
                                            {{ $task->deadline_at ? $task->deadline_at->timezone(\App\Models\Task::TIMEZONE)->translatedFormat('d M Y, H:i') : '-' }}
                                        </td>
                                        <td class="py-4 px-5 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeClass }}">
                                                {{ $badgeText }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
                                            <a
                                                href="{{ route('intern.tasks.show', $task) }}"
                                                class="inline-block px-3 py-1.5 text-xs font-semibold text-[#015987] border border-slate-200 rounded-lg hover:border-[#015987] hover:bg-sky-50 transition"
                                            >
                                                {{ $actionLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Tabel -->
                    <div class="px-6 py-4 border-t border-slate-100 text-xs text-slate-400">
                        Menampilkan {{ $internDashboard['upcoming_tasks']->count() }} dari {{ $internDashboard['active_tasks_count'] }} tugas aktif terdekat
                    </div>
                @endif
            </div>

        </div>

        <!-- ========================================================== -->
        <!-- KOLOM KANAN (LEBAR 5/12): STATUS PENGAJUAN IZIN & NOTIFIKASI -->
        <!-- ========================================================== -->
        <div class="lg:col-span-5 space-y-8">
            
            <!-- KARTU 1: STATUS PENGAJUAN IZIN -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                <!-- Header Kartu -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-[#015987]">Status Pengajuan Izin</h3>
                        <p class="text-[11px] text-slate-400">Riwayat permohonan terkini</p>
                    </div>
                    @if($dailySummary['pending_leaves_count'] > 0)
                        <span class="text-[11px] font-bold text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                            {{ $dailySummary['pending_leaves_count'] }} Pending
                        </span>
                    @else
                        <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">
                            Terkini
                        </span>
                    @endif
                </div>

                @if($dailySummary['latest_leave'])
                    @php $leave = $dailySummary['latest_leave']; @endphp
                    <!-- Box Permohonan Terkini -->
                    <div class="p-5 rounded-xl border {{ $leave->isPending() ? 'border-amber-200 bg-amber-50/20' : ($leave->isApproved() ? 'border-emerald-200 bg-emerald-50/20' : 'border-rose-200 bg-rose-50/20') }} space-y-3.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold {{ $leave->isSick() ? 'text-rose-600 bg-rose-50 border-rose-200' : 'text-sky-600 bg-sky-50 border-sky-200' }} px-2 py-0.5 rounded border">
                                {{ strtoupper($leave->leave_type) }} ({{ $leave->duration_days }} HARI)
                            </span>
                            @if($leave->isPending())
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded uppercase">
                                    Pending
                                </span>
                            @elseif($leave->isApproved())
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded uppercase">
                                    Disetujui
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded uppercase">
                                    Ditolak
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-700 font-medium leading-relaxed">
                            <strong class="text-[#015987]">{{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}:</strong>
                            {{ $leave->reason }}
                        </p>

                        @if($leave->review_notes)
                            <div class="p-2.5 rounded-lg bg-white/80 border border-slate-200 text-[11px] text-slate-600">
                                <span class="font-bold text-slate-700">Catatan HR:</span> {{ $leave->review_notes }}
                            </div>
                        @endif

                        <!-- Lampiran File -->
                        @if($leave->evidence)
                            <a href="{{ route('media.private.show', $leave->evidence_media_id) }}" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 bg-white rounded-xl border border-slate-200/80 text-xs text-slate-600 hover:border-slate-300 transition group">
                                <span class="flex items-center gap-2 truncate text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#015987]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="truncate">{{ $leave->evidence->original_name }}</span>
                                </span>
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @endif

                        <p class="text-[11px] text-slate-400">
                            Diajukan: {{ \Carbon\Carbon::parse($leave->created_at)->translatedFormat('d M Y') }}
                        </p>

                        <a href="{{ route('intern.leave.index') }}" class="w-full py-2.5 border border-[#015987] hover:bg-sky-50 text-[#015987] font-semibold text-xs rounded-xl transition text-center block">
                            Lihat Status Lengkap
                        </a>
                    </div>
                @else
                    <div class="py-8 text-center space-y-3">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Belum pernah mengajukan permohonan izin/sakit.</p>
                        <a href="{{ route('intern.leave.index') }}" class="inline-block px-4 py-2 bg-[#015987] hover:bg-sky-700 text-white text-xs font-semibold rounded-xl transition">
                            Ajukan Izin / Sakit
                        </a>
                    </div>
                @endif
            </div>

            <!-- KARTU 2: NOTIFIKASI TERBARU -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-[#015987]">Notifikasi Terbaru</h3>
                    @if (($internDashboard['notifications']['count'] ?? 0) > 0)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                            {{ $internDashboard['notifications']['count'] }} Aktif
                        </span>
                    @endif
                </div>

                @if (empty($internDashboard['notifications']['items']))
                    <div class="py-6 text-center space-y-1">
                        <p class="text-xs font-semibold text-slate-500">Tidak ada notifikasi baru</p>
                        <p class="text-[11px] text-slate-400">Semua tugas dan logbook Anda dalam kondisi baik.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($internDashboard['notifications']['items'] as $notif)
                            @php
                                $iconBox = match ($notif['type']) {
                                    'danger' => 'bg-rose-50 text-rose-500',
                                    'warning' => 'bg-amber-50 text-amber-600',
                                    default => 'bg-sky-50 text-sky-600',
                                };
                                $isDbNotif = $notif['is_database'] ?? false;
                            @endphp
                            @if ($isDbNotif)
                                <form method="POST" action="{{ route('notifications.read', $notif['id']) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-start gap-3.5 p-3.5 rounded-xl bg-sky-50/40 border border-sky-100 hover:border-sky-200 hover:bg-sky-50/70 transition cursor-pointer">
                                        <div class="w-8 h-8 rounded-lg {{ $iconBox }} flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if ($notif['type'] === 'danger')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                @elseif ($notif['type'] === 'warning')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                @endif
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <h4 class="text-xs font-bold text-[#015987]">{{ $notif['title'] }}</h4>
                                                <span class="w-2 h-2 rounded-full bg-[#015987] flex-shrink-0"></span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $notif['message'] }}</p>
                                            @if (! empty($notif['meta']))
                                                <p class="text-[10px] font-semibold text-slate-400 mt-1">{{ $notif['meta'] }}</p>
                                            @endif
                                        </div>
                                    </button>
                                </form>
                            @else
                                <a
                                    href="{{ $notif['url'] }}"
                                    class="flex items-start gap-3.5 p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-sky-200 hover:bg-sky-50/30 transition"
                                >
                                    <div class="w-8 h-8 rounded-lg {{ $iconBox }} flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if ($notif['type'] === 'danger')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            @elseif ($notif['type'] === 'warning')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            @endif
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-[#015987]">{{ $notif['title'] }}</h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $notif['message'] }}</p>
                                        @if (! empty($notif['meta']))
                                            <p class="text-[10px] font-semibold text-slate-400 mt-1">{{ $notif['meta'] }}</p>
                                        @endif
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif

                <!-- Footer Notifikasi -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <a href="{{ route('notifications.index') }}" class="font-semibold text-slate-500 hover:text-[#015987] transition">
                        Semua Notifikasi
                    </a>
                    <a href="{{ route('intern.tasks.index') }}" class="font-semibold text-[#015987] hover:underline text-xs flex items-center gap-1">
                        Semua Tugas &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
