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
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['active_interns'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">Peserta</span>
                </div>
                <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    <span>+{{ $stats['new_this_month'] }} bulan ini</span>
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
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['active_pics'] }}</span>
                    <span class="text-xs text-slate-500 font-medium">PIC</span>
                </div>
                <p class="text-[11px] font-semibold text-teal-600 mt-1.5 flex items-center gap-1 truncate">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500 flex-shrink-0"></span>
                    <span class="truncate">{{ $stats['pic_divisions'] }}</span>
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
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['total_active'] }}</span>
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
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $stats['inactive_users'] }}</span>
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
                    <h3 class="text-sm font-bold text-slate-900">Daftar Peserta Magang</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pembaruan data terkini dari database sistem</p>
                </div>
                <a href="{{ route('admin.intern-profiles.index') }}" class="text-xs font-semibold text-[#015987] hover:underline flex items-center gap-1">
                    Lihat Semua &rsaquo;
                </a>
            </div>

            <!-- Tabel Data Peserta Real dari Database -->
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
                        @forelse($recentInterns as $index => $intern)
                            @php
                                $avatarColors = ['bg-sky-100 text-sky-700', 'bg-rose-100 text-rose-600', 'bg-purple-100 text-purple-700', 'bg-emerald-100 text-emerald-700'];
                                $avatarClass = $avatarColors[$index % 4];
                                $profile = $intern->internProfile;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <!-- Nama Peserta -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full font-bold text-xs flex items-center justify-center flex-shrink-0 {{ $avatarClass }}">
                                            {{ strtoupper(substr(preg_replace('/\s+/', ' ', trim($intern->name)), 0, 1)) }}{{ strtoupper(substr(strstr(preg_replace('/\s+/', ' ', trim($intern->name)), ' ') ?: ' ', 1, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs leading-none">{{ $intern->name }}</p>
                                            <p class="text-[10px] text-slate-400 mt-1">{{ $intern->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Asal Institusi -->
                                <td class="py-4 px-5 font-medium text-slate-700">
                                    {{ $profile?->institution ?? 'Belum diatur' }}
                                </td>

                                <!-- Periode Magang -->
                                <td class="py-4 px-5 text-slate-600">
                                    {{ $profile?->formatted_period ?? 'Belum diatur' }}
                                </td>

                                <!-- Status Akun -->
                                <td class="py-4 px-5">
                                    @if($intern->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                            <span class="w-1 h-1 rounded-full bg-slate-400"></span>
                                            Selesai
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-6 text-right">
                                    <a 
                                        href="{{ route('admin.intern-profiles.edit', $intern) }}"
                                        class="px-2.5 py-1 text-[11px] font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition inline-block"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400">
                                    Belum ada data peserta magang yang terdaftar di database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Tabel Dinamis -->
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Menampilkan {{ $recentInterns->count() }} dari {{ $stats['active_interns'] }} peserta aktif</span>
                <span class="font-semibold text-sky-600">
                    {{ $stats['total_active'] > 0 ? round(($stats['active_interns'] / $stats['total_active']) * 100) : 0 }}% proporsi peserta magang
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
                <span class="text-[11px] font-semibold text-sky-600 bg-sky-50 px-2.5 py-0.5 rounded-full">
                    Sistem Aktif
                </span>
            </div>

            <!-- Item 1: Periode Magang Mendekati Selesai -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Masa Magang Mendekati Akhir
                    </span>
                    <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">
                        {{ $stats['near_end_interns'] }} Peserta
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    {{ $stats['near_end_interns'] }} peserta magang terdata akan mengakhiri masa magang dalam 30 hari ke depan.
                </p>
                <div class="pt-1">
                    <a href="{{ route('admin.intern-profiles.index') }}" class="block text-center py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                        Kelola Profil Peserta Magang &rarr;
                    </a>
                </div>
            </div>

            <!-- Item 2: Ringkasan Pengelolaan Akun Pengguna -->
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#015987]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Kelola Akses &amp; Akun
                    </span>
                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                        {{ $stats['total_active'] }} Aktif
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    Total {{ $stats['total_active'] }} akun aktif (Admin, PIC, dan Intern) terotorisasi login ke sistem WANTRACE.
                </p>
                <div class="pt-1">
                    <a href="{{ route('admin.users.index') }}" class="block text-center py-2 bg-[#015987] hover:bg-[#014467] text-white text-xs font-bold rounded-xl transition">
                        Buka Manajemen Akun &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
