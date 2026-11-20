@extends('layouts.app')

@section('title', 'Presensi Peserta Bimbingan - PIC')
@section('page-title', 'Presensi Peserta Bimbingan')

@section('page-badge')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-sky-100 text-[#015987] border border-sky-200">
        Mode Pemantauan (Read-Only)
    </span>
@endsection

@section('page-subtitle', 'PT WAN Teknologi Internasional — Pengawasan Kehadiran Peserta yang Aktif Ditugaskan kepada Anda')
@section('search-placeholder', 'Cari peserta bimbingan...')

@section('content')
<div class="space-y-8">

    <!-- Banner Kebijakan Wewenang PIC (Read-Only Sesuai Aturan Bisnis) -->
    <div class="p-4 rounded-2xl bg-sky-50/70 border border-sky-200 text-[#015987] text-xs font-semibold flex items-center gap-3 shadow-xs">
        <svg class="w-5 h-5 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="font-normal text-slate-700">
            <strong class="font-bold text-[#015987]">Hak Akses PIC:</strong> Anda memiliki wewenang untuk melihat kehadiran dan bukti foto selfie peserta magang yang menjadi tanggung jawab aktif bimbingan Anda. Koreksi status presensi resmi berada di bawah otoritas Admin/HR.
        </p>
    </div>

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN BIMBINGAN HARI INI               -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Total Peserta Bimbingan Aktif -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Peserta Bimbingan Aktif</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $summary['total_assigned_interns'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Peserta</span>
                </div>
                <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    Penugasan PIC aktif
                </p>
            </div>
        </div>

        <!-- Card 2: Peserta Hadir -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Sudah Presensi Masuk</span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $summary['total_present'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Peserta</span>
                </div>
                <p class="text-[11px] font-semibold text-teal-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    Presensi hari ini tervalidasi
                </p>
            </div>
        </div>

        <!-- Card 3: Tepat Waktu -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Tepat Waktu</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $summary['on_time_count'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500 font-medium">Peserta</span>
                </div>
                <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Check-in &le; 08:05:59 WIB
                </p>
            </div>
        </div>

        <!-- Card 4: Terlambat / Izin Sah -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Terlambat &amp; Izin</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $summary['late_count'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500 font-medium">Terlambat</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                    <span>{{ $summary['approved_leaves']->count() }} memiliki izin/sakit sah</span>
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. FILTER TANGGAL PEMANTAUAN                                   -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6">
        <form method="GET" action="{{ route('pic.attendance.index') }}" class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div class="space-y-1.5 w-full sm:w-72">
                <label for="filterDate" class="block text-xs font-bold text-slate-700">Pilih Tanggal Presensi</label>
                <input 
                    type="date" 
                    id="filterDate" 
                    name="date" 
                    value="{{ $selectedDate }}" 
                    class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987]"
                >
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    Tampilkan Data
                </button>
                <a href="{{ route('pic.attendance.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                    Hari Ini
                </a>
            </div>
        </form>
    </div>

    <!-- ============================================================== -->
    <!-- 3. TABEL PRESENSI PESERTA BIMBINGAN                            -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
        
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-[#015987]">
                    Daftar Kehadiran Peserta Bimbingan ({{ Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }})
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Hanya menampilkan peserta magang yang ditugaskan secara aktif kepada akun Anda
                </p>
            </div>

            <span class="text-xs font-semibold text-sky-700 bg-sky-50 px-3 py-1 rounded-xl border border-sky-200/80">
                Waktu Server: {{ $now->format('H:i') }} WIB
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6">Nama Peserta Magang</th>
                        <th class="py-4 px-5">Waktu Check-In (WIB)</th>
                        <th class="py-4 px-5 text-center">Foto Bukti</th>
                        <th class="py-4 px-5">Status Kehadiran</th>
                        <th class="py-4 px-5">Catatan Peserta</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($summary['attendances'] as $row)
                        <tr class="hover:bg-slate-50/70 transition">
                            
                            <!-- Peserta Magang -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($row->intern->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">{{ $row->intern->name }}</p>
                                        <p class="text-[10px] text-slate-400 mt-1">{{ $row->intern->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Waktu Masuk WIB -->
                            <td class="py-4 px-5 font-semibold text-slate-700">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $row->checked_in_at->format('H:i:s') }}
                                </span>
                            </td>

                            <!-- Foto Bukti Selfie -->
                            <td class="py-4 px-5 text-center">
                                <div class="w-9 h-9 rounded-lg bg-slate-200 mx-auto overflow-hidden border border-slate-200 cursor-pointer hover:ring-2 hover:ring-[#015987] transition shadow-2xs"
                                     onclick="openImagePreviewModal('{{ route('media.private.show', $row->photo_media_id) }}', '{{ $row->intern->name }} - {{ $row->checked_in_at->format('H:i') }} WIB')">
                                    <img src="{{ route('media.private.show', $row->photo_media_id) }}" alt="Selfie" class="w-full h-full object-cover">
                                </div>
                            </td>

                            <!-- Status Presensi -->
                            <td class="py-4 px-5">
                                @if($row->isRejected())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border bg-rose-50 text-rose-700 border-rose-200">
                                        <span class="w-1 h-1 rounded-full bg-rose-600"></span>
                                        Ditolak HR
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $row->isOnTime() ? 'bg-teal-50 text-teal-600 border-teal-200' : 'bg-rose-50 text-rose-600 border-rose-200' }}">
                                        <span class="w-1 h-1 rounded-full {{ $row->isOnTime() ? 'bg-teal-500' : 'bg-rose-500' }}"></span>
                                        {{ $row->isOnTime() ? 'Tepat Waktu' : 'Terlambat' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Catatan Peserta -->
                            <td class="py-4 px-5 text-slate-500 truncate max-w-xs">
                                {{ $row->notes ?: '-' }}
                            </td>

                            <!-- Aksi Read-Only (Hanya Preview Foto) -->
                            <td class="py-4 px-6 text-right">
                                <button 
                                    type="button" 
                                    onclick="openImagePreviewModal('{{ route('media.private.show', $row->photo_media_id) }}', '{{ $row->intern->name }} - {{ $row->checked_in_at->format('H:i') }} WIB')"
                                    class="px-3 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                    Lihat Foto
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <p class="text-xs font-semibold text-slate-600">Belum Ada Presensi Masuk</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Peserta bimbingan Anda belum melakukan presensi masuk pada tanggal ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- Modal Preview Foto Selfie Resolusi Penuh -->
<div id="imagePreviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white max-w-md w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h5 class="text-xs font-bold text-[#015987]" id="previewModalTitle">Bukti Foto Selfie Peserta</h5>
            <button type="button" onclick="closeImagePreviewModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-4 bg-slate-900 flex items-center justify-center">
            <img id="previewModalImg" src="" alt="Selfie" class="max-h-[400px] w-auto object-contain rounded-lg">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openImagePreviewModal(url, title) {
        const modal = document.getElementById('imagePreviewModal');
        const img = document.getElementById('previewModalImg');
        const titleEl = document.getElementById('previewModalTitle');
        img.src = url;
        if (titleEl) titleEl.innerText = title;
        modal.classList.remove('hidden');
    }

    function closeImagePreviewModal() {
        document.getElementById('imagePreviewModal').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImagePreviewModal();
        }
    });
</script>
@endpush
