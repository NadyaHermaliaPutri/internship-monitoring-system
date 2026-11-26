@extends('layouts.app')

@section('title', 'Dashboard Bimbingan PIC')
@section('page-title', 'Dashboard Bimbingan PIC')

@section('page-badge')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-sky-100 text-[#015987] border border-sky-200">
        Model Penugasan Bersama (Multi-PIC)
    </span>
@endsection

@section('page-subtitle', 'PT WAN Teknologi Internasional — Pengawasan Teknis & Progres Pekerjaan Proyek')
@section('search-placeholder', 'Cari tugas, subjek, atau intern...')

@section('content')
<div class="space-y-8">

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN PIC (TOP 4 STAT CARDS) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Card 1: Tugas Aktif Anda -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Tugas Aktif Anda</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $picDashboard['active_tasks_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                @if ($picDashboard['overdue_tasks_count'] > 0)
                    <p class="text-[11px] font-semibold text-rose-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        {{ $picDashboard['overdue_tasks_count'] }} melewati deadline (Overdue)
                    </p>
                @else
                    <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                        Dikerjakan {{ $picDashboard['active_interns_count'] }} peserta magang
                    </p>
                @endif
            </div>
        </div>

        <!-- Card 2: Mendekati Tenggat -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Mendekati Tenggat</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $picDashboard['approaching_tasks_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <p class="text-[11px] font-semibold text-amber-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Batas waktu &le; 24 Jam
                </p>
            </div>
        </div>

        <!-- Card 3: Butuh Review Anda -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Butuh Review Anda</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $picDashboard['awaiting_review_tasks_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Kiriman Tugas</span>
                </div>
                <a href="{{ route('pic.logbooks.index', ['status' => 'pending']) }}" class="text-[11px] font-semibold text-emerald-600 hover:underline mt-1.5 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $picDashboard['pending_logbooks_count'] }} logbook menunggu review
                </a>
            </div>
        </div>

        <!-- Card 4: Dalam Proses Revisi -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Dalam Proses Revisi</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $picDashboard['revision_tasks_count'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Menunggu peserta kirim ulang
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. GRID 2 KOLOM (KONTEN UTAMA PIC) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ========================================================== -->
        <!-- KOLOM KIRI (LEBAR 7/12): SUBMISI & PESERTA DENGAN TUGAS -->
        <!-- ========================================================== -->
        <div class="lg:col-span-7 space-y-8">

            <!-- WIDGET MODUL 2: PRESENSI & IZIN ANAK BIMBINGAN HARI INI (READ-ONLY PIC) -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-[#015987]">Presensi &amp; Izin Anak Bimbingan Hari Ini</h3>
                            <span class="text-[10px] font-semibold text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-full">
                                Read-Only PIC
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Status kehadiran real-time khusus peserta aktif dalam bimbingan Anda ({{ $mentoringSummary['total_assigned_interns'] }} peserta)
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('pic.attendance.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
                            Riwayat Presensi &rsaquo;
                        </a>
                        <a href="{{ route('pic.leave.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
                            Riwayat Izin &rsaquo;
                        </a>
                    </div>
                </div>

                @if(! ($mentoringSummary['day_status']['is_working_day'] ?? true))
                    <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 text-xs text-slate-600 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Hari ini adalah <strong>{{ $mentoringSummary['day_status']['label'] ?? 'Hari Libur' }}</strong>. Tidak ada kewajiban presensi regular.</span>
                    </div>
                @endif

                <div class="p-6">
                    @if($mentoringSummary['total_assigned_interns'] === 0)
                        <div class="text-center py-6 text-xs text-slate-400">
                            Anda belum memiliki peserta magang aktif yang ditugaskan ke akun Anda saat ini.
                        </div>
                    @else
                        <!-- Ringkasan Cepat -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Total Bimbingan</span>
                                <span class="text-lg font-bold text-slate-800">{{ $mentoringSummary['total_assigned_interns'] }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-100">
                                <span class="text-[10px] text-emerald-600 block font-semibold uppercase">Sudah Hadir</span>
                                <span class="text-lg font-bold text-emerald-700">{{ $mentoringSummary['total_present'] }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-amber-50/50 border border-amber-100">
                                <span class="text-[10px] text-amber-600 block font-semibold uppercase">Izin / Sakit Sah</span>
                                <span class="text-lg font-bold text-amber-700">{{ $mentoringSummary['approved_leaves']->count() }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-rose-50/50 border border-rose-100">
                                <span class="text-[10px] text-rose-600 block font-semibold uppercase">Belum Check-In</span>
                                <span class="text-lg font-bold text-rose-700">
                                    {{ max(0, $mentoringSummary['total_assigned_interns'] - $mentoringSummary['total_present'] - $mentoringSummary['approved_leaves']->count()) }}
                                </span>
                            </div>
                        </div>

                        <!-- Daftar Peserta Bimbingan Hari Ini -->
                        <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                            @foreach($mentoringSummary['assigned_interns'] as $intern)
                                @php
                                    $att = $mentoringSummary['attendances']->firstWhere('intern_user_id', $intern->id);
                                    $leave = $mentoringSummary['approved_leaves']->firstWhere('intern_user_id', $intern->id);
                                @endphp
                                <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/60 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-sky-100 text-[#015987] font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr($intern->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-[#015987] leading-tight">{{ $intern->name }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $intern->email }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if($att)
                                            @if($att->isRejected())
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                    Ditolak HR (Perlu Foto Ulang)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $att->isOnTime() ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $att->isOnTime() ? 'bg-teal-500' : 'bg-rose-500' }}"></span>
                                                    {{ $att->isOnTime() ? 'Hadir Tepat Waktu' : 'Terlambat' }} ({{ \Carbon\Carbon::parse($att->checked_in_at)->timezone(config('wantrace.timezone', 'Asia/Jakarta'))->format('H:i') }} WIB)
                                                </span>
                                            @endif
                                            @if($att->photo_media_id)
                                                <a href="{{ route('media.private.show', $att->photo_media_id) }}" target="_blank" class="p-1 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-800 text-[10px] font-medium" title="Lihat Foto Bukti">
                                                    Foto Bukti
                                                </a>
                                            @endif
                                        @elseif($leave)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                {{ $leave->isSick() ? 'Sakit Resmi' : 'Izin Resmi' }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Belum Check-In
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- KARTU 1: SUBMISI MENUNGGU REVIEW ANDA -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <!-- Header Kartu -->
                <div class="p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-[#015987]">Submisi Menunggu Review Anda</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Hasil pengerjaan tugas buatan Anda yang siap diperiksa</p>
                    </div>
                    <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200/80 px-3 py-1 rounded-full">
                        {{ $picDashboard['awaiting_review_tasks_count'] }} Menunggu Keputusan
                    </span>
                </div>

                <!-- Daftar Submisi Asli -->
                <div class="p-6 space-y-4">
                    @if ($picDashboard['submissions_awaiting_review']->isEmpty())
                        <div class="py-6 text-center space-y-1">
                            <p class="text-xs font-bold text-slate-600">Tidak ada submisi tugas yang menunggu review</p>
                            <p class="text-[11px] text-slate-400">Kiriman hasil tugas peserta buatan Anda akan muncul di sini.</p>
                        </div>
                    @else
                        @foreach ($picDashboard['submissions_awaiting_review'] as $task)
                            @php
                                $sub = $task->latestSubmission;
                                $internName = $task->intern?->name ?? 'Peserta';
                                $initials = strtoupper(substr($internName, 0, 2));
                                $institution = $picDashboard['submission_institutions']->get($task->intern_user_id);
                            @endphp
                            <div class="p-5 rounded-xl border border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="space-y-2 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-[#015987] text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <span class="text-xs font-bold text-[#015987]">{{ $internName }}</span>
                                        @if ($sub)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $sub->submission_number > 1 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-sky-50 text-sky-600 border-sky-200' }} border">
                                                Versi {{ $sub->submission_number }}{{ $sub->submission_number > 1 ? ' (Revisi)' : '' }}
                                            </span>
                                        @endif
                                        @if ($institution)
                                            <span class="text-[11px] text-slate-400">{{ $institution }}</span>
                                        @endif
                                    </div>

                                    <h4 class="text-xs font-bold text-[#015987]">{{ $task->title }}</h4>

                                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                        @if ($sub?->submission_url)
                                            <span class="inline-flex items-center gap-1 text-slate-600 truncate max-w-xs">
                                                {{ \Illuminate\Support\Str::limit($sub->submission_url, 45) }}
                                            </span>
                                            <span class="text-slate-300">&bull;</span>
                                        @elseif ($sub?->evidenceMedia)
                                            <span class="inline-flex items-center gap-1 text-slate-600">
                                                {{ $sub->evidenceMedia->original_name }} ({{ $sub->evidenceMedia->formatted_size }})
                                            </span>
                                            <span class="text-slate-300">&bull;</span>
                                        @endif
                                        <span class="text-slate-400">
                                            Submit: {{ ($sub?->submitted_at ?? $task->updated_at)->timezone(\App\Models\Task::TIMEZONE)->translatedFormat('d M Y, H:i') }} WIB
                                        </span>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    <a
                                        href="{{ route('pic.tasks.show', $task) }}"
                                        class="inline-block px-4 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl transition shadow-xs"
                                    >
                                        Periksa &amp; Review
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if ($picDashboard['pending_logbooks']->isNotEmpty())
                        <div class="pt-4 border-t border-slate-100 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold text-slate-700">
                                    Logbook Menunggu Review ({{ $picDashboard['pending_logbooks_count'] }})
                                </p>
                                <a href="{{ route('pic.logbooks.index', ['status' => 'pending']) }}" class="text-[11px] font-semibold text-[#015987] hover:underline">
                                    Lihat Semua &rsaquo;
                                </a>
                            </div>
                            <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 overflow-hidden">
                                @foreach ($picDashboard['pending_logbooks'] as $lb)
                                    <div class="px-4 py-3 bg-white flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-800 truncate">{{ $lb->title }}</p>
                                            <p class="text-[11px] text-slate-400">
                                                {{ $lb->intern?->name ?? 'Peserta' }} &bull; {{ $lb->log_date->translatedFormat('d M Y') }}
                                            </p>
                                        </div>
                                        <a
                                            href="{{ route('pic.logbooks.show', $lb) }}"
                                            class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold shrink-0 transition"
                                        >
                                            Review
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- KARTU 2: PESERTA DENGAN TUGAS DARI ANDA -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <!-- Header Kartu -->
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-[#015987]">Peserta dengan Tugas dari Anda</h3>
                            <span class="text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                Multi-PIC
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Peserta aktif dalam bimbingan Anda atau yang sedang memegang tugas buatan Anda</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a
                            href="{{ route('pic.tasks.index') }}"
                            class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-xl bg-slate-50 hover:bg-slate-100 transition"
                        >
                            Semua Peserta Terkait Anda ({{ $picDashboard['related_interns_count'] }})
                        </a>
                        <a
                            href="{{ route('pic.tasks.create') }}"
                            class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#015987] hover:bg-[#01486e] rounded-xl transition flex items-center gap-1 shadow-xs"
                        >
                            <span>+</span>
                            <span>Buat Tugas</span>
                        </a>
                    </div>
                </div>

                @if ($picDashboard['related_interns']->isEmpty())
                    <div class="p-8 text-center space-y-2">
                        <p class="text-xs font-bold text-slate-600">Belum ada peserta terkait saat ini</p>
                        <p class="text-[11px] text-slate-400">Mulai dengan membuat tugas baru untuk peserta magang aktif.</p>
                    </div>
                @else
                    <!-- Tabel Data Peserta & Tugas Asli -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="py-4 px-6">Peserta Magang</th>
                                    <th class="py-4 px-5">Tugas Terbaru Anda (Status)</th>
                                    <th class="py-4 px-5">Tugas Aktif</th>
                                    <th class="py-4 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach ($picDashboard['related_interns'] as $row)
                                    @php
                                        $intern = $row['intern'];
                                        $latestTask = $row['latest_task'];
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-4 px-6">
                                            <div>
                                                <p class="font-bold text-[#015987] text-xs leading-none">{{ $intern->name }}</p>
                                                <p class="text-[10px] text-slate-400 mt-1">{{ $row['institution'] }}</p>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5">
                                            @if ($latestTask)
                                                @php
                                                    $taskBadge = match ($latestTask->status->value) {
                                                        'submitted', 'resubmitted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        'revision_requested' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                        'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                                    };
                                                @endphp
                                                <div class="space-y-1">
                                                    <a href="{{ route('pic.tasks.show', $latestTask) }}" class="font-medium text-[#015987] hover:underline text-xs block">
                                                        {{ $latestTask->title }}
                                                    </a>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $taskBadge }}">
                                                        {{ $latestTask->status->label() }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-xs italic text-slate-400">Belum ada tugas aktif</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-5 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $row['active_tasks_count'] > 0 ? 'bg-sky-50 text-[#015987] border-sky-200' : 'bg-slate-100 text-slate-600 border-slate-200' }} border">
                                                {{ $row['active_tasks_count'] }} Tugas Aktif
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
                                            <a
                                                href="{{ route('pic.tasks.index', ['intern_id' => $intern->id]) }}"
                                                class="inline-block px-2.5 py-1 text-[11px] font-semibold text-[#015987] border border-slate-200 rounded-lg hover:border-[#015987] hover:bg-sky-50 transition"
                                            >
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        <!-- ========================================================== -->
        <!-- KOLOM KANAN (LEBAR 5/12): EWS & TENGGAT TUGAS -->
        <!-- ========================================================== -->
        <div class="lg:col-span-5 space-y-8">

            <!-- KARTU 1: SINYAL PERINGATAN (EWS) -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                <!-- Header Kartu -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#015987]">Sinyal Peringatan (EWS)</h3>
                            <p class="text-[11px] text-slate-400">Tugas overdue &amp; revisi berulang buatan Anda</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold {{ count($picDashboard['ews_signals']) > 0 ? 'text-rose-500 bg-rose-50 border-rose-200' : 'text-emerald-600 bg-emerald-50 border-emerald-200' }} border px-2.5 py-0.5 rounded-full">
                        {{ count($picDashboard['ews_signals']) }} Sinyal
                    </span>
                </div>

                @if (empty($picDashboard['ews_signals']))
                    <div class="py-6 text-center space-y-1">
                        <p class="text-xs font-bold text-slate-600">Tidak ada sinyal peringatan saat ini</p>
                        <p class="text-[11px] text-slate-400">Seluruh tugas buatan Anda berjalan sesuai tenggat waktu.</p>
                    </div>
                @else
                    @foreach ($picDashboard['ews_signals'] as $signal)
                        @php $isOverdueSignal = $signal['type'] === 'overdue'; @endphp
                        <div class="p-5 rounded-xl border {{ $isOverdueSignal ? 'border-rose-100 bg-rose-50/30' : 'border-amber-100 bg-amber-50/30' }} space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-bold {{ $isOverdueSignal ? 'text-rose-600 bg-rose-50 border-rose-200' : 'text-amber-700 bg-amber-50 border-amber-200' }} px-2 py-0.5 rounded border">
                                    {{ $signal['badge'] }}
                                </span>
                                <span class="text-xs font-bold {{ $isOverdueSignal ? 'text-rose-500' : 'text-amber-600' }}">
                                    {{ $signal['tag'] }}
                                </span>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold text-[#015987]">{{ $signal['title'] }}</h4>
                                <p class="text-xs text-slate-500 mt-1">
                                    Peserta: <span class="font-medium text-slate-700">{{ $signal['intern_name'] }}</span>
                                </p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $signal['detail'] }}</p>
                            </div>

                            <div class="pt-2.5 border-t {{ $isOverdueSignal ? 'border-rose-100/60' : 'border-amber-100/60' }} flex items-center justify-between text-xs">
                                <span class="text-[11px] text-slate-400">Tindakan:</span>
                                <a href="{{ $signal['action_url'] }}" class="font-bold {{ $isOverdueSignal ? 'text-[#015987]' : 'text-amber-700' }} hover:underline text-xs">
                                    {{ $signal['action_label'] }} &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- KARTU 2: TENGGAT TUGAS BUATAN ANDA -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-[#015987]">Tenggat Tugas Buatan Anda</h3>
                    <a href="{{ route('pic.tasks.index') }}" class="text-[11px] font-semibold text-[#015987] hover:underline">
                        Semua Tugas &rsaquo;
                    </a>
                </div>

                @if ($picDashboard['upcoming_deadlines']->isEmpty())
                    <div class="py-6 text-center space-y-1">
                        <p class="text-xs font-bold text-slate-600">Tidak ada tenggat tugas aktif</p>
                        <p class="text-[11px] text-slate-400">Tugas aktif buatan Anda dengan batas waktu akan tampil di sini.</p>
                    </div>
                @else
                    @php
                        $nowPic = \Illuminate\Support\Carbon::now(\App\Models\Task::TIMEZONE);
                    @endphp
                    <div class="space-y-3">
                        @foreach ($picDashboard['upcoming_deadlines'] as $task)
                            @php
                                $isOverdue = $task->isOverdue($nowPic);
                                $hoursLeft = $isOverdue ? 0 : (int) $nowPic->diffInHours($task->deadline_at);
                                if ($isOverdue) {
                                    $pillText = 'Overdue';
                                    $pillClass = 'text-rose-600 bg-rose-50 border-rose-200';
                                } elseif ($hoursLeft <= 24) {
                                    $pillText = max(1, $hoursLeft).' Jam';
                                    $pillClass = 'text-amber-600 bg-amber-50 border-amber-200';
                                } else {
                                    $daysLeft = max(1, (int) ceil($hoursLeft / 24));
                                    $pillText = $daysLeft.' Hari';
                                    $pillClass = 'text-slate-600 bg-slate-100 border-slate-200';
                                }
                            @endphp
                            <a
                                href="{{ route('pic.tasks.show', $task) }}"
                                class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-sky-200 hover:bg-sky-50/30 transition"
                            >
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-[#015987] truncate">{{ $task->title }}</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $task->intern?->name ?? 'Peserta' }} &bull; {{ $task->deadline_at->timezone(\App\Models\Task::TIMEZONE)->translatedFormat('d M Y, H:i') }} WIB
                                    </p>
                                </div>
                                <span class="text-xs font-bold border px-2.5 py-1 rounded-lg shrink-0 {{ $pillClass }}">
                                    {{ $pillText }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
