@extends('layouts.app')

@section('title', 'Izin & Sakit Peserta Bimbingan - PIC')
@section('page-title', 'Pemantauan Izin & Sakit Peserta Bimbingan')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Akses Read-Only Khusus Pembimbing Lapangan (PIC)')

@section('content')
<div class="space-y-8">

    <!-- Flash Alert Sukses / Error -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- 1. BANNER PEMBERITAHUAN AKSES READ-ONLY PIC                     -->
    <!-- ============================================================== -->
    <div class="p-5 rounded-2xl bg-sky-50/80 border border-sky-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-[#015987] text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-[#015987]">Akses Pemantauan Khusus Pembimbing (Read-Only)</h4>
                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                    Anda hanya dapat melihat permohonan izin/sakit dari peserta yang saat ini <strong class="text-slate-800">aktif ditugaskan</strong> kepada Anda. Otoritas persetujuan atau penolakan pengajuan sepenuhnya merupakan kewenangan Administrator/HR.
                </p>
            </div>
        </div>
        <div class="flex-shrink-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-[#015987] text-xs font-bold border border-sky-200/80 shadow-2xs">
                <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>{{ count($assignedInternUserIds) }} Peserta Bimbingan</span>
            </span>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. FILTER STATUS & JENIS                                        -->
    <!-- ============================================================== -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-xs">
        <form method="GET" action="{{ route('pic.leave.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            
            <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                
                <!-- Filter Status -->
                <div class="w-full sm:w-64">
                    <select 
                        name="status" 
                        class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs"
                    >
                        <option value="">Semua Status Pengajuan</option>
                        <option value="pending" {{ ($statusFilter ?? '') === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi (Pending)</option>
                        <option value="approved" {{ ($statusFilter ?? '') === 'approved' ? 'selected' : '' }}>Disetujui HR (Approved)</option>
                        <option value="rejected" {{ ($statusFilter ?? '') === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                    </select>
                </div>

                <!-- Filter Jenis -->
                <div class="w-full sm:w-64">
                    <select 
                        name="type" 
                        class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs"
                    >
                        <option value="">Semua Jenis (Izin & Sakit)</option>
                        <option value="sick" {{ ($typeFilter ?? '') === 'sick' ? 'selected' : '' }}>Sakit (Surat Dokter)</option>
                        <option value="leave" {{ ($typeFilter ?? '') === 'leave' ? 'selected' : '' }}>Izin Keperluan</option>
                    </select>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl transition shadow-2xs flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter</span>
                </button>

                @if($statusFilter || $typeFilter)
                    <a href="{{ route('pic.leave.index') }}" class="w-full sm:w-auto text-center px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold rounded-xl transition">
                        Reset
                    </a>
                @endif

            </div>

            <div class="text-xs text-slate-400 font-medium text-right">
                Menampilkan <strong class="text-slate-700">{{ $leaveRequests->total() }}</strong> riwayat pengajuan
            </div>

        </form>
    </div>

    <!-- ============================================================== -->
    <!-- 3. TABEL DAFTAR IZIN / SAKIT PESERTA BIMBINGAN                  -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
        
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Daftar Pengajuan Izin & Sakit Peserta Bimbingan Aktif</h3>
                <p class="text-xs text-slate-400 mt-0.5">Pemantauan riwayat ketidakhadiran resmi untuk koordinasi penugasan harian</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">Peserta Bimbingan</th>
                        <th class="py-3 px-4">Jenis</th>
                        <th class="py-3 px-4">Periode Tanggal</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4">Alasan Pengajuan</th>
                        <th class="py-3 px-4 text-center">Bukti Pendukung</th>
                        <th class="py-3 px-4">Status & Keputusan HR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
                    @forelse($leaveRequests as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            
                            <!-- Peserta -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#015987]/10 text-[#015987] font-bold flex items-center justify-center flex-shrink-0 text-xs">
                                        {{ strtoupper(substr($item->intern->name ?? 'P', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $item->intern->name ?? 'Peserta' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $item->intern->email ?? '-' }}</p>
                                        @if($item->intern->institution_name ?? false)
                                            <p class="text-[10px] text-sky-700 font-medium">{{ $item->intern->institution_name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Jenis -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($item->isSick())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Sakit
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-sky-800 border border-sky-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#015987]"></span>
                                        Izin
                                    </span>
                                @endif
                            </td>

                            <!-- Periode Tanggal -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <p class="font-semibold text-slate-800">{{ $item->start_date->translatedFormat('d M Y') }}</p>
                                @if($item->start_date->ne($item->end_date))
                                    <p class="text-[11px] text-slate-400">s/d {{ $item->end_date->translatedFormat('d M Y') }}</p>
                                @endif
                            </td>

                            <!-- Durasi -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[11px]">
                                    {{ $item->getDurationDays() }} Hari
                                </span>
                            </td>

                            <!-- Alasan -->
                            <td class="py-4 px-4 max-w-[240px]">
                                <p class="line-clamp-2 text-slate-600 text-xs" title="{{ $item->reason }}">
                                    {{ $item->reason }}
                                </p>
                            </td>

                            <!-- Bukti Pendukung (Private Media) -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if($item->evidence)
                                    @php
                                        $mediaUrl = route('media.private.show', $item->evidence->id);
                                        $isPdf = $item->evidence->isPdf();
                                    @endphp
                                    <button 
                                        type="button" 
                                        onclick="openEvidenceModal('{{ $mediaUrl }}', '{{ addslashes($item->evidence->original_name) }}', '{{ $item->evidence->readable_file_size }}', {{ $isPdf ? 'true' : 'false' }}, '{{ addslashes($item->intern->name ?? 'Peserta') }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-sky-200 bg-sky-50 hover:bg-sky-100/70 text-[#015987] text-xs font-semibold transition"
                                    >
                                        @if($isPdf)
                                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <span>PDF Dokumen</span>
                                        @else
                                            <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Lihat Gambar</span>
                                        @endif
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Tidak ada berkas</span>
                                @endif
                            </td>

                            <!-- Status & Keputusan HR -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($item->isPending())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        <svg class="w-3 h-3 text-amber-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Menunggu Review HR
                                    </span>
                                @elseif($item->isApproved())
                                    <div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Disetujui HR
                                        </span>
                                        @if($item->review_notes)
                                            <p class="text-[10px] text-slate-400 mt-1 max-w-[200px] truncate" title="{{ $item->review_notes }}">
                                                Catatan: {{ $item->review_notes }}
                                            </p>
                                        @endif
                                    </div>
                                @elseif($item->isRejected())
                                    <div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                            <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Ditolak HR
                                        </span>
                                        @if($item->review_notes)
                                            <p class="text-[10px] text-slate-400 mt-1 max-w-[200px] truncate" title="{{ $item->review_notes }}">
                                                Catatan: {{ $item->review_notes }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-600">Tidak ada pengajuan izin/sakit dari peserta bimbingan Anda</p>
                                    <p class="text-[11px] text-slate-400">Pengajuan akan muncul otomatis saat peserta yang aktif ditugaskan mengajukan izin/sakit.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($leaveRequests->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $leaveRequests->links() }}
            </div>
        @endif

    </div>

</div>

<!-- ============================================================== -->
<!-- MODAL: PREVIEW BUKTI PRIVAT (GAMBAR / PDF)                      -->
<!-- ============================================================== -->
<div id="evidencePreviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white max-w-2xl w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[90vh]">
        
        <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <div>
                <h5 class="text-xs font-bold text-[#015987]" id="previewModalTitle">Bukti Surat / Pendukung</h5>
                <p id="previewModalMeta" class="text-[10px] text-slate-400 mt-0.5 font-medium">-</p>
            </div>
            <div class="flex items-center gap-2">
                <a id="previewModalDownload" href="" target="_blank" class="px-3 py-1 bg-sky-50 text-[#015987] hover:bg-sky-100 text-xs font-semibold rounded-lg border border-sky-200 transition inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>Tab Baru</span>
                </a>
                <button type="button" onclick="closeEvidenceModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-4 bg-slate-900 flex-1 overflow-auto flex items-center justify-center min-h-[300px]">
            <!-- Preview Gambar -->
            <img id="previewImage" src="" alt="Bukti" class="max-h-[500px] w-auto object-contain rounded-lg hidden">
            
            <!-- Preview Dokumen PDF -->
            <div id="previewPdfContainer" class="w-full h-[500px] hidden">
                <iframe id="previewPdfIframe" src="" class="w-full h-full rounded-lg bg-white border-0"></iframe>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    function openEvidenceModal(url, filename, filesize, isPdf, internName) {
        const modal = document.getElementById('evidencePreviewModal');
        const titleEl = document.getElementById('previewModalTitle');
        const metaEl = document.getElementById('previewModalMeta');
        const downloadLink = document.getElementById('previewModalDownload');
        const img = document.getElementById('previewImage');
        const pdfContainer = document.getElementById('previewPdfContainer');
        const pdfIframe = document.getElementById('previewPdfIframe');

        titleEl.innerText = `Bukti Izin/Sakit: ${internName}`;
        metaEl.innerText = `${filename} • ${filesize}`;
        downloadLink.href = url;

        if (isPdf) {
            img.classList.add('hidden');
            img.src = '';
            pdfContainer.classList.remove('hidden');
            pdfIframe.src = url;
        } else {
            pdfContainer.classList.add('hidden');
            pdfIframe.src = '';
            img.classList.remove('hidden');
            img.src = url;
        }

        modal.classList.remove('hidden');
    }

    function closeEvidenceModal() {
        const modal = document.getElementById('evidencePreviewModal');
        const img = document.getElementById('previewImage');
        const pdfIframe = document.getElementById('previewPdfIframe');
        img.src = '';
        pdfIframe.src = '';
        modal.classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEvidenceModal();
        }
    });
</script>
@endpush
