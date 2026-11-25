@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi')

@section('content')
<div class="space-y-6">
    <!-- 1. Header Halaman -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-medium mb-1">
                <span>Admin HR</span>
                <span>&bull;</span>
                <span class="text-[#015987] font-semibold">Laporan &amp; Rekapitulasi</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan &amp; Rekapitulasi Evaluasi</h1>
            <p class="text-xs text-slate-500 mt-1">Rekapitulasi data historis presensi, izin, logbook, tugas, dan monitoring komposit peserta magang.</p>
        </div>

        <!-- Tombol Aksi Ekspor -->
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.reports.export-pdf', ['report_type' => $reportType, 'intern_id' => $internId, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export PDF</span>
            </a>

            <a href="{{ route('admin.reports.export-excel', ['report_type' => $reportType, 'intern_id' => $internId, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m-9 5h14a2 2 0 002-2V7a2 2 0 00-2-2H9.5a2 2 0 00-1.414.586l-4.5 4.5A2 2 0 003 11.5V19a2 2 0 002 2z"/>
                </svg>
                <span>Export Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- 2. Form Filter Laporan -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 items-end">
            <!-- Jenis Laporan -->
            <div class="space-y-1.5">
                <label for="report_type" class="block text-xs font-bold text-slate-700">Jenis Laporan <span class="text-rose-500">*</span></label>
                <select name="report_type" id="report_type" class="w-full text-xs rounded-xl border-slate-300 shadow-2xs focus:border-[#015987] focus:ring focus:ring-[#015987]/15">
                    <option value="attendance" {{ $reportType === 'attendance' ? 'selected' : '' }}>Rekap Presensi</option>
                    <option value="leave" {{ $reportType === 'leave' ? 'selected' : '' }}>Rekap Izin &amp; Sakit</option>
                    <option value="logbook" {{ $reportType === 'logbook' ? 'selected' : '' }}>Rekap Logbook Manual</option>
                    <option value="task" {{ $reportType === 'task' ? 'selected' : '' }}>Rekap Tugas Magang</option>
                    <option value="monitoring" {{ $reportType === 'monitoring' ? 'selected' : '' }}>Rekap Monitoring Peserta</option>
                </select>
            </div>

            <!-- Cakupan Peserta Magang -->
            <div class="space-y-1.5">
                <label for="intern_id" class="block text-xs font-bold text-slate-700">Cakupan Peserta</label>
                <select name="intern_id" id="intern_id" class="w-full text-xs rounded-xl border-slate-300 shadow-2xs focus:border-[#015987] focus:ring focus:ring-[#015987]/15">
                    <option value="">Semua Peserta Magang</option>
                    @foreach($interns as $intern)
                        <option value="{{ $intern->id }}" {{ $internId == $intern->id ? 'selected' : '' }}>
                            {{ $intern->name }} {{ ! $intern->is_active ? '(Nonaktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Awal -->
            <div class="space-y-1.5">
                <label for="start_date" class="block text-xs font-bold text-slate-700">Tanggal Awal <span class="text-rose-500">*</span></label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" required
                       class="w-full text-xs rounded-xl border-slate-300 shadow-2xs focus:border-[#015987] focus:ring focus:ring-[#015987]/15">
            </div>

            <!-- Tanggal Akhir -->
            <div class="space-y-1.5">
                <label for="end_date" class="block text-xs font-bold text-slate-700">Tanggal Akhir <span class="text-rose-500">*</span></label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" required
                       class="w-full text-xs rounded-xl border-slate-300 shadow-2xs focus:border-[#015987] focus:ring focus:ring-[#015987]/15">
            </div>

            <!-- Tombol Submit -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 px-4 text-xs font-semibold rounded-xl bg-[#015987] hover:bg-[#014467] text-white shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Tampilkan</span>
                </button>
                <a href="{{ route('admin.reports.index') }}" class="p-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 transition" title="Reset Filter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <!-- 3. Metadata Header Info Banner -->
    <div class="bg-gradient-to-r from-sky-50 via-slate-50 to-white rounded-2xl border border-sky-100 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#015987]/10 text-[#015987] mb-2 uppercase tracking-wide">
                {{ $reportData['metadata']['report_title'] ?? 'Laporan' }}
            </div>
            <div class="text-sm font-semibold text-slate-800">
                Periode: <span class="font-bold text-[#015987]">{{ \Illuminate\Support\Carbon::parse($startDate)->translatedFormat('d M Y') }}</span> s.d. <span class="font-bold text-[#015987]">{{ \Illuminate\Support\Carbon::parse($endDate)->translatedFormat('d M Y') }}</span>
                <span class="text-slate-500 font-normal">({{ $reportData['metadata']['working_days_count'] ?? 0 }} Hari Kerja Efektif)</span>
            </div>
            <div class="text-xs text-slate-500 mt-1">
                Cakupan: <strong class="text-slate-700">{{ $reportData['metadata']['intern_scope_label'] ?? 'Semua Peserta' }}</strong>
            </div>
        </div>
        <div class="text-left md:text-right text-xs text-slate-400">
            <div>Waktu Pembuatan:</div>
            <div class="font-medium text-slate-600">{{ $reportData['metadata']['generated_at']?->translatedFormat('d F Y, H:i') }} WIB</div>
        </div>
    </div>

    <!-- 4. Ringkasan Statistik (Summary Cards) -->
    @if($reportType === 'attendance')
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-slate-500 uppercase">Hari Kerja</span>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $reportData['summary']['working_days_count'] ?? 0 }}</p>
                <span class="text-[10px] text-slate-400">Dalam periode</span>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-emerald-700 uppercase">Hadir Sah</span>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $reportData['summary']['total_valid_presence'] ?? 0 }}</p>
                <span class="text-[10px] text-slate-400">Non-rejected</span>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-sky-700 uppercase">Tepat Waktu</span>
                <p class="text-2xl font-extrabold text-[#015987] mt-1">{{ $reportData['summary']['on_time_count'] ?? 0 }}</p>
                <span class="text-[10px] text-slate-400">&lt; 08:05 WIB</span>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-amber-700 uppercase">Terlambat</span>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $reportData['summary']['late_count'] ?? 0 }}</p>
                <span class="text-[10px] text-slate-400">&ge; 08:05 WIB</span>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-rose-700 uppercase">Ditolak HR</span>
                <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $reportData['summary']['rejected_count'] ?? 0 }}</p>
                <span class="text-[10px] text-slate-400">Presensi invalid</span>
            </div>
        </div>
    @elseif($reportType === 'leave')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-slate-500 uppercase">Total Pengajuan</span>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $reportData['summary']['total_requests'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-emerald-700 uppercase">Disetujui</span>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $reportData['summary']['approved_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-amber-700 uppercase">Menunggu Review</span>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $reportData['summary']['pending_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-rose-700 uppercase">Ditolak</span>
                <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $reportData['summary']['rejected_count'] ?? 0 }}</p>
            </div>
        </div>
    @elseif($reportType === 'logbook')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-slate-500 uppercase">Total Logbook Manual</span>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $reportData['summary']['total_entries'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-emerald-700 uppercase">Disetujui</span>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $reportData['summary']['approved_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-amber-700 uppercase">Menunggu Review</span>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $reportData['summary']['pending_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-rose-700 uppercase">Perlu Revisi</span>
                <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $reportData['summary']['revision_requested_count'] ?? 0 }}</p>
            </div>
        </div>
    @elseif($reportType === 'task')
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-slate-500 uppercase">Total Tugas</span>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $reportData['summary']['total_tasks'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-emerald-700 uppercase">Selesai (Accepted)</span>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $reportData['summary']['accepted_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-sky-700 uppercase">In Progress</span>
                <p class="text-2xl font-extrabold text-sky-600 mt-1">{{ $reportData['summary']['in_progress_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-amber-700 uppercase">Submitted</span>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $reportData['summary']['submitted_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-rose-700 uppercase">Overdue</span>
                <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $reportData['summary']['overdue_count'] ?? 0 }}</p>
            </div>
        </div>
    @elseif($reportType === 'monitoring')
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3.5">
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-slate-500 uppercase">Total Peserta Dievaluasi</span>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $reportData['summary']['total_interns'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-4">
                <span class="text-[11px] font-bold text-[#015987] uppercase">Hari Kerja Efektif</span>
                <p class="text-2xl font-extrabold text-[#015987] mt-1">{{ $reportData['summary']['working_days_count'] ?? 0 }}</p>
            </div>
            <div class="bg-sky-50 rounded-xl border border-sky-200 p-4 flex flex-col justify-center">
                <span class="text-[11px] font-bold text-sky-800 uppercase">Prinsip Evaluasi</span>
                <p class="text-xs text-sky-900 mt-1 leading-snug">Data murni agregasi faktual untuk bahan pertimbangan peninjauan manusia oleh HR.</p>
            </div>
        </div>
    @endif

    <!-- 5. Tabel Preview Data -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Preview Data Laporan</h2>
            <span class="text-xs text-slate-400">Menampilkan seluruh rekaman sesuai filter aktif</span>
        </div>

        <div class="overflow-x-auto">
            @if($reportType === 'attendance')
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold text-slate-600">
                            <th class="py-3 px-4 text-center w-12">No</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Peserta Magang</th>
                            <th class="py-3 px-4">Jam Masuk</th>
                            <th class="py-3 px-4">Status Presensi</th>
                            <th class="py-3 px-4">Verifikasi HR</th>
                            <th class="py-3 px-4">Peninjau / Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reportData['attendances'] as $idx => $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $row->attendance_date?->translatedFormat('d M Y') }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $row->intern?->name ?? 'Peserta #' . $row->intern_user_id }}</td>
                                <td class="py-3 px-4 font-mono text-slate-600">{{ $row->checked_in_at?->format('H:i:s') ?? '-' }} WIB</td>
                                <td class="py-3 px-4">
                                    @if($row->isOnTime())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Tepat Waktu</span>
                                    @elseif($row->isLate())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Terlambat</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">{{ $row->status }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($row->verification_status === 'verified')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Terverifikasi</span>
                                    @elseif($row->verification_status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">Submitted</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    <div>{{ $row->notes ?: '-' }}</div>
                                    @if($row->reviewer)
                                        <div class="text-[10px] text-slate-400 mt-0.5">Oleh: {{ $row->reviewer->name }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Tidak ada catatan presensi pada filter terpilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($reportType === 'leave')
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold text-slate-600">
                            <th class="py-3 px-4 text-center w-12">No</th>
                            <th class="py-3 px-4">Peserta Magang</th>
                            <th class="py-3 px-4">Jenis</th>
                            <th class="py-3 px-4">Rentang Tanggal</th>
                            <th class="py-3 px-4">Alasan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Peninjau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reportData['leave_requests'] as $idx => $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $row->intern?->name ?? 'Peserta #' . $row->intern_user_id }}</td>
                                <td class="py-3 px-4">
                                    @if($row->type === 'sick')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Sakit</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">Izin</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-700">
                                    {{ $row->start_date?->translatedFormat('d M Y') }} s.d. {{ $row->end_date?->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 max-w-xs">{{ $row->reason }}</td>
                                <td class="py-3 px-4">
                                    @if($row->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Approved</span>
                                    @elseif($row->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Rejected</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    @if($row->reviewer)
                                        <div class="font-medium text-slate-700">{{ $row->reviewer->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $row->reviewed_at?->translatedFormat('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Tidak ada pengajuan izin atau sakit pada filter terpilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($reportType === 'logbook')
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold text-slate-600">
                            <th class="py-3 px-4 text-center w-12">No</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Peserta Magang</th>
                            <th class="py-3 px-4">Judul &amp; Aktivitas</th>
                            <th class="py-3 px-4">Hasil / Pencapaian</th>
                            <th class="py-3 px-4">Status Approval</th>
                            <th class="py-3 px-4">Peninjau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reportData['logbooks'] as $idx => $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $row->log_date?->translatedFormat('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $row->intern?->name ?? 'Peserta #' . $row->intern_user_id }}</div>
                                    @if(($row->revisions_count ?? 0) > 0)
                                        <div class="text-[10px] font-semibold text-amber-600 mt-0.5">{{ $row->revisions_count }}x revisi</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 max-w-sm">
                                    <div class="font-bold text-slate-800">{{ $row->title }}</div>
                                    <div class="text-slate-500 text-[11px] mt-0.5 line-clamp-2">{{ $row->activity_description }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 max-w-xs">{{ $row->result ?: '-' }}</td>
                                <td class="py-3 px-4">
                                    @if($row->status?->value === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Approved</span>
                                    @elseif($row->status?->value === 'revision_requested')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Perlu Revisi</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">Pending</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    @if($row->reviewer)
                                        <div class="font-medium text-slate-700">{{ $row->reviewer->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $row->reviewed_at?->translatedFormat('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Tidak ada logbook manual pada filter terpilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($reportType === 'task')
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold text-slate-600">
                            <th class="py-3 px-4 text-center w-12">No</th>
                            <th class="py-3 px-4">Judul Tugas</th>
                            <th class="py-3 px-4">Peserta Penerima</th>
                            <th class="py-3 px-4">PIC Pemberi</th>
                            <th class="py-3 px-4">Penugasan &amp; Deadline</th>
                            <th class="py-3 px-4">Pengumpulan &amp; Review</th>
                            <th class="py-3 px-4">Status Terkini</th>
                            <th class="py-3 px-4">Penyelesaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reportData['tasks'] as $idx => $row)
                            @php 
                                $isOverdue = $row->isOverdue(); 
                                $latestSub = $row->latestSubmission;
                                $latestRev = $latestSub?->latestReview;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 max-w-xs">
                                    <div class="font-bold text-slate-900">{{ $row->title }}</div>
                                    @if($row->description)
                                        <div class="text-slate-400 text-[11px] line-clamp-1 mt-0.5">{{ $row->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $row->intern?->name ?? 'Peserta #' . $row->intern_user_id }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $row->pic?->name ?? 'PIC #' . $row->pic_user_id }}</td>
                                <td class="py-3 px-4">
                                    <div class="text-slate-500 text-[11px]">Diberikan: {{ $row->assigned_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                    <div class="font-semibold {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                        Deadline: {{ $row->deadline_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($row->submissions_count > 0 && $latestSub)
                                        <div class="font-medium text-slate-700">
                                            Versi ke-{{ $latestSub->submission_number }} (Total {{ $row->submissions_count }}x)
                                        </div>
                                        <div class="text-[10px] text-slate-400">{{ $latestSub->submitted_at?->translatedFormat('d/m/Y H:i') }}</div>
                                        @if($latestRev)
                                            <div class="text-[10px] mt-0.5 font-semibold {{ $latestRev->decision?->value === 'approved' ? 'text-emerald-600' : 'text-amber-600' }}">
                                                Review: {{ $latestRev->decision?->label() ?? $latestRev->decision?->value }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-[11px]">Belum dikumpulkan</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        @if($row->status?->value === 'accepted')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Accepted</span>
                                        @elseif($row->status?->value === 'in_progress')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">In Progress</span>
                                        @elseif($row->status?->value === 'submitted')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Submitted</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">{{ $row->status?->value }}</span>
                                        @endif

                                        @if($isOverdue)
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-rose-600 text-white animate-pulse">OVERDUE</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    @if($row->accepted_at)
                                        <div class="font-medium text-emerald-700">{{ $row->accepted_at->translatedFormat('d M Y, H:i') }}</div>
                                        @if($row->acceptedByUser)
                                            <div class="text-[10px] text-slate-400">Oleh: {{ $row->acceptedByUser->name }}</div>
                                        @endif
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Tidak ada catatan tugas pada filter terpilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($reportType === 'monitoring')
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold text-slate-600">
                            <th class="py-3 px-4 text-center w-12" rowspan="2">No</th>
                            <th class="py-3 px-4" rowspan="2">Nama Peserta Magang</th>
                            <th class="py-3 px-4 text-center" rowspan="2">Status Akun</th>
                            <th class="py-2 px-3 text-center border-l border-r border-slate-200" colspan="4">Presensi ({{ $reportData['metadata']['working_days_count'] ?? 0 }} Hari Kerja)</th>
                            <th class="py-3 px-4 text-center" rowspan="2">Izin/Sakit (Hari)</th>
                            <th class="py-2 px-3 text-center border-l border-r border-slate-200" colspan="2">Logbook Manual</th>
                            <th class="py-2 px-3 text-center border-l border-slate-200" colspan="3">Tugas Magang</th>
                        </tr>
                        <tr class="bg-slate-100/60 border-b border-slate-200/80 text-[10px] font-bold text-slate-500">
                            <th class="py-2 px-3 text-center text-emerald-700 border-l border-slate-200">Hadir Sah</th>
                            <th class="py-2 px-3 text-center text-sky-700">Tepat</th>
                            <th class="py-2 px-3 text-center text-amber-700">Telat</th>
                            <th class="py-2 px-3 text-center text-rose-700 border-r border-slate-200">Ditolak</th>
                            <th class="py-2 px-3 text-center border-l border-slate-200">Total</th>
                            <th class="py-2 px-3 text-center text-emerald-700 border-r border-slate-200">Approved</th>
                            <th class="py-2 px-3 text-center border-l border-slate-200">Total</th>
                            <th class="py-2 px-3 text-center text-emerald-700">Selesai</th>
                            <th class="py-2 px-3 text-center text-rose-700">Overdue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reportData['intern_summaries'] as $idx => $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $row['intern']->name }}</td>
                                <td class="py-3 px-4 text-center">
                                    @if($row['is_active'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-emerald-700 border-l border-slate-100">{{ $row['valid_presence_count'] }}</td>
                                <td class="py-3 px-3 text-center text-sky-700 font-medium">{{ $row['on_time_count'] }}</td>
                                <td class="py-3 px-3 text-center text-amber-700 font-medium">{{ $row['late_count'] }}</td>
                                <td class="py-3 px-3 text-center text-rose-700 font-medium border-r border-slate-100">{{ $row['rejected_count'] }}</td>
                                <td class="py-3 px-4 text-center text-slate-700 font-medium">{{ $row['approved_leave_days'] }}</td>
                                <td class="py-3 px-3 text-center text-slate-700 font-medium border-l border-slate-100">{{ $row['logbooks_total'] }}</td>
                                <td class="py-3 px-3 text-center text-emerald-700 font-bold border-r border-slate-100">{{ $row['logbooks_approved'] }}</td>
                                <td class="py-3 px-3 text-center text-slate-700 font-medium border-l border-slate-100">{{ $row['tasks_total'] }}</td>
                                <td class="py-3 px-3 text-center text-emerald-700 font-bold">{{ $row['tasks_accepted'] }}</td>
                                <td class="py-3 px-3 text-center {{ $row['tasks_overdue'] > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                    {{ $row['tasks_overdue'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="py-12 text-center text-slate-400">Tidak ada peserta magang yang ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
