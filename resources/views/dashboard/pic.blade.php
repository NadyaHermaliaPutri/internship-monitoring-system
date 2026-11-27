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
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">8</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    Dikerjakan 5 peserta magang
                </p>
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
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">3</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <p class="text-[11px] font-semibold text-amber-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Batas waktu &lt; 24 Jam
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
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">3</span>
                    <span class="text-xs text-slate-500 font-medium">Kiriman</span>
                </div>
                <p class="text-[11px] font-semibold text-emerald-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Khusus tugas buatan Anda
                </p>
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
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">2</span>
                    <span class="text-xs text-slate-500 font-medium">Tugas</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Menunggu intern kirim ulang
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

            <!-- KARTU 1: SUBMISI MENUNGGU REVIEW ANDA -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs overflow-hidden">
                <!-- Header Kartu -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-[#015987]">Submisi Menunggu Review Anda</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Hasil pengerjaan tugas yang Anda berikan dan siap diuji</p>
                    </div>
                    <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200/80 px-3 py-1 rounded-full">
                        3 Menunggu Keputusan
                    </span>
                </div>

                <!-- Daftar Submisi -->
                <div class="p-6 space-y-4">
                    
                    <!-- Submisi 1: Nadya Hermalia -->
                    <div class="p-5 rounded-xl border border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-[#015987] text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                    NH
                                </div>
                                <span class="text-xs font-bold text-[#015987]">Nadya Hermalia</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-600 border border-sky-200">
                                    Versi 1
                                </span>
                                <span class="text-[11px] text-slate-400">
                                    Politeknik Negeri Padang
                                </span>
                            </div>

                            <h4 class="text-xs font-bold text-[#015987]">
                                Slicing UI Responsive Dashboard Web &amp; PWA
                            </h4>

                            <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                <span class="inline-flex items-center gap-1 text-slate-600 hover:text-[#015987] cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    github.com/nadya/intern-ui
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-400">Submit: 08:35 WIB</span>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <button class="px-4 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl transition shadow-xs">
                                Periksa &amp; Review
                            </button>
                        </div>
                    </div>

                    <!-- Submisi 2: Mutiara F. R. -->
                    <div class="p-5 rounded-xl border border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-600 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                    MF
                                </div>
                                <span class="text-xs font-bold text-[#015987]">Mutiara F. R.</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    Versi 3 (Revisi)
                                </span>
                                <span class="text-[11px] text-slate-400">
                                    Politeknik Negeri Padang
                                </span>
                            </div>

                            <h4 class="text-xs font-bold text-[#015987]">
                                Implementasi Endpoint Sanctum &amp; Session Auth
                            </h4>

                            <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                <span class="inline-flex items-center gap-1 text-slate-600 hover:text-[#015987] cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    video_demo_auth.mp4 (9.4 MB)
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-400">Submit: Kemarin 16:45 WIB</span>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <button class="px-4 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl transition shadow-xs">
                                Periksa &amp; Review
                            </button>
                        </div>
                    </div>

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
                                Pool Bersama
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Menampilkan peserta aktif yang sedang memegang tugas buatan Anda dan beban PIC lainnya</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
                            Semua Peserta Terkait Anda (5)
                        </button>
                        <button class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#015987] hover:bg-[#01486e] rounded-xl transition flex items-center gap-1 shadow-xs">
                            <span>+</span>
                            <span>Buat Tugas</span>
                        </button>
                    </div>
                </div>

                <!-- Tabel Data Peserta & Tugas -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-4 px-6">Peserta Magang</th>
                                <th class="py-4 px-5">Tugas Anda (Status)</th>
                                <th class="py-4 px-5">PIC Lain yang Terlibat</th>
                                <th class="py-4 px-5">Beban Kerja</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            
                            <!-- Row 1: Nadya Hermalia -->
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">Nadya Hermalia</p>
                                        <p class="text-[10px] text-slate-400 mt-1">Politeknik Negeri Padang</p>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="space-y-1">
                                        <p class="font-medium text-[#015987] text-xs">Slicing UI Responsive Dashboard</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                            SUBMITTED
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-5 h-5 rounded-full bg-purple-100 text-purple-700 font-bold text-[9px] flex items-center justify-center">
                                            SA
                                        </div>
                                        <span class="text-xs text-slate-600">Sarah (DB Migration)</span>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-600 border border-amber-200">
                                        2 Tugas Aktif
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                        Detail
                                    </button>
                                </td>
                            </tr>

                            <!-- Row 2: Atika Naira -->
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-bold text-[#015987] text-xs leading-none">Atika Naira</p>
                                        <p class="text-[10px] text-slate-400 mt-1">Politeknik Negeri Padang</p>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="space-y-1">
                                        <p class="font-medium text-[#015987] text-xs">Integrasi Service Worker PWA</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-600 border border-sky-200">
                                            IN_PROGRESS
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="text-xs italic text-slate-400">Tidak ada (Hanya Anda)</span>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        1 Tugas Aktif
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                        Detail
                                    </button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
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
                            <p class="text-[11px] text-slate-400">Peringatan keterlambatan &amp; asistensi</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-rose-500 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full">
                        2 Sinyal
                    </span>
                </div>

                <!-- Item EWS 1: Tugas Overdue -->
                <div class="p-5 rounded-xl border border-rose-100 bg-rose-50/30 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                            TUGAS OVERDUE
                        </span>
                        <span class="text-xs font-bold text-rose-500">
                            Terlambat 1 Hari
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-[#015987]">
                            Seeder &amp; Database Dummy Testing
                        </h4>
                        <p class="text-xs text-slate-500 mt-1">
                            Peserta: <span class="font-medium text-slate-700">Hanifa R.</span>
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Tenggat terlewat: 02 Nov 2026, 17:00 WIB.
                        </p>
                    </div>

                    <div class="pt-2.5 border-t border-rose-100/60 flex items-center justify-between text-xs">
                        <span class="text-[11px] text-slate-400">Tindakan disarankan:</span>
                        <a href="javascript:void(0)" class="font-bold text-[#015987] hover:underline text-xs">
                            Perpanjang / Hubungi
                        </a>
                    </div>
                </div>

                <!-- Item EWS 2: Revisi Berulang -->
                <div class="p-5 rounded-xl border border-amber-100 bg-amber-50/30 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                            REVISI BERULANG (3X)
                        </span>
                        <span class="text-xs font-bold text-amber-600">
                            Butuh Diskusi
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-[#015987]">
                            Endpoint Sanctum &amp; Session Auth
                        </h4>
                        <p class="text-xs text-slate-500 mt-1">
                            Peserta: <span class="font-medium text-slate-700">Mutiara F. R.</span>
                        </p>
                        <p class="text-[11px] italic text-slate-500 bg-white/70 p-2.5 rounded-lg border border-amber-100/80 mt-2 leading-relaxed">
                            "Sinyal untuk percakapan/pendampingan langsung, bukan hukuman otomatis [p.8]."
                        </p>
                    </div>

                    <div class="pt-2.5 border-t border-amber-100/60 flex items-center justify-between text-xs">
                        <span class="text-[11px] text-slate-400">Asistensi Teknis:</span>
                        <a href="javascript:void(0)" class="font-bold text-amber-700 hover:underline text-xs">
                            Buka Chat / Diskusi
                        </a>
                    </div>
                </div>

            </div>

            <!-- KARTU 2: TENGGAT TUGAS BUATAN ANDA -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-4">
                <h3 class="text-sm font-bold text-[#015987] pb-3 border-b border-slate-100">
                    Tenggat Tugas Buatan Anda
                </h3>

                <div class="space-y-3.5">
                    
                    <!-- Item 1 -->
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-slate-200 transition">
                        <div>
                            <h4 class="text-xs font-bold text-[#015987]">Integrasi PWA Manifest</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Atika Naira • Besok, 17:00 WIB</p>
                        </div>
                        <span class="text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg">
                            24 Jam
                        </span>
                    </div>

                    <!-- Item 2 -->
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-slate-200 transition">
                        <div>
                            <h4 class="text-xs font-bold text-[#015987]">Unit Testing Middleware Auth</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Mutiara F. R. • Kamis, 17:00 WIB</p>
                        </div>
                        <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">
                            2 Hari
                        </span>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>
@endsection
