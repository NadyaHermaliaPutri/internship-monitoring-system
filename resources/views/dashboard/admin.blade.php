@extends('layouts.app')

@section('title', 'Dashboard Monitoring Magang - Admin')
@section('page-title', 'Dashboard Monitoring Magang')
@section('page-subtitle', 'PT WAN Teknologi Internasional')
@section('search-placeholder', 'Cari peserta...')

@section('content')
<div class="space-y-8">

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN (100% DINAMIS DARI DATABASE)      -->
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
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $adminSummary['total_active_interns'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Peserta</span>
                </div>
                <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    Aktif Terdaftar
                </p>
            </div>
        </div>

        <!-- Card 2: PIC Mentor Aktif -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">PIC Mentor Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $adminSummary['total_present'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Hadir</span>
                </div>
                <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    {{ $adminSummary['pending_leaves_count'] }} pending izin &bull; {{ $adminSummary['on_time_count'] }} tepat waktu
                </p>
            </div>
        </div>

        <!-- Card 3: Total Akun Aktif -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Total Akun Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['total_active'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500 font-medium">Akun</span>
                </div>
                <p class="text-[11px] font-semibold text-indigo-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>Dapat login ke sistem</span>
                </p>
            </div>
        </div>

        <!-- Card 4: Akun Dinonaktifkan / Alumni -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-slate-700">Akun Nonaktif / Selesai</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['inactive_users'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500 font-medium">Alumni</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-400 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                    <span>Histori data tetap utuh</span>
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. GRID 2 KOLOM (DATA PESERTA MAGANG & RINGKASAN MONITORING)   -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- KOLOM KIRI (LEBAR 7/12): TABEL PESERTA MAGANG DINAMIS -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
            <!-- Header Kartu -->
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#015987]">Presensi Harian Peserta</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Tanggal: {{ \Carbon\Carbon::parse($adminSummary['date'])->translatedFormat('l, d F Y') }}</p>
                </div>
                <a href="{{ route('admin.attendance.index', ['date' => $adminSummary['date']]) }}" class="text-xs font-semibold text-[#015987] hover:underline flex items-center gap-1">
                    Lihat Semua &rsaquo;
                </a>
            </div>

            @if(! ($adminSummary['day_status']['is_working_day'] ?? true))
                <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-2 text-xs text-slate-600">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Hari ini adalah <strong>{{ $adminSummary['day_status']['label'] ?? 'Hari Libur' }}</strong>. Tidak ada kewajiban presensi reguler.</span>
                </div>
            @endif

            <!-- Tabel Data -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-4 px-6">Nama Peserta</th>
                            <th class="py-4 px-5">Asal Institusi</th>
                            <th class="py-4 px-5">Periode Magang</th>
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
                                        <svg class="w-3.5 h-3.5 {{ $att->isLate() ? 'text-rose-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($att->checked_in_at)->timezone(config('wantrace.timezone', 'Asia/Jakarta'))->format('H:i') }}
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
                                    Belum ada peserta yang melakukan presensi hari ini.
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

        <!-- KOLOM KANAN (LEBAR 5/12): STATUS MONITORING & PENUGASAN -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
            <!-- Header Kartu -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Status Monitoring Magang</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Ringkasan operasional dan penugasan sistem</p>
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
                <!-- Item Permohonan Izin -->
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

                    <!-- Tombol Lampiran Dokumen -->
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

                    <!-- Tombol Aksi: Setujui & Tolak -->
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

    </div>

</div>

<!-- Modal Tolak Permohonan Izin -->
<div id="rejectModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
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
