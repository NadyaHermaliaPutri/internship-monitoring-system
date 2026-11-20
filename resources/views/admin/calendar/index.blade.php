@extends('layouts.app')

@section('title', 'Kalender Kerja & Hari Libur - Admin')
@section('page-title', 'Kalender Kerja & Hari Libur Perusahaan')
@section('page-subtitle', 'PT WAN Teknologi Internasional — Manajemen Hari Operasional & Pengecualian Kalender')
@section('search-placeholder', 'Cari tanggal atau hari libur...')

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

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1 shadow-xs">
            <div class="flex items-center gap-2 text-rose-700 font-bold">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Terdapat kesalahan pada input pengecualian kalender:</span>
            </div>
            <ul class="list-disc list-inside pl-6 text-rose-600 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- 1. KARTU STATISTIK RINGKASAN KALENDER BULAN INI                 -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Hari Kerja Resmi Bulan Ini -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Hari Kerja Resmi</span>
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $totalWorkingDays }}</span>
                    <span class="text-xs text-slate-500 font-medium">Hari Kerja</span>
                </div>
                <p class="text-[11px] font-semibold text-teal-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    Dari {{ $totalCalendarDays }} hari kalender bulan {{ $currentMonthDate->translatedFormat('F') }}
                </p>
            </div>
        </div>

        <!-- Card 2: Hari Libur & Akhir Pekan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Hari Libur &amp; Weekend</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $totalHolidays }}</span>
                    <span class="text-xs text-slate-500 font-medium">Hari Libur</span>
                </div>
                <p class="text-[11px] font-semibold text-rose-500 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Presensi otomatis tidak aktif
                </p>
            </div>
        </div>

        <!-- Card 3: Pengecualian Kalender Khusus -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Pengecualian Khusus (HR)</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#015987] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#015987]">{{ $monthlyExceptions->count() }}</span>
                    <span class="text-xs text-slate-500 font-medium">Override Tanggal</span>
                </div>
                <p class="text-[11px] font-semibold text-sky-600 mt-1.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    Penetapan manual oleh Admin/HR
                </p>
            </div>
        </div>

        <!-- Card 4: Hari Ini & Waktu Server -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-xs flex flex-col justify-between min-h-[148px]">
            <div class="flex items-start justify-between">
                <span class="text-xs font-bold text-[#015987]">Status Hari Ini</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-5">
                @php
                    $todayStatus = app(\App\Services\WorkCalendarService::class)->getDayStatus(\Carbon\Carbon::now(config('wantrace.timezone', 'Asia/Jakarta')));
                @endphp
                <div class="flex items-baseline gap-1.5">
                    <span class="text-lg sm:text-xl font-bold {{ $todayStatus['is_working_day'] ? 'text-teal-600' : 'text-rose-500' }}">
                        {{ $todayStatus['label'] }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium mt-1 truncate" title="{{ $todayStatus['reason'] }}">
                    {{ $todayStatus['reason'] }}
                </p>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 2. GRID UTAMA: KALENDER VISUAL (KIRI) & DAFTAR OVERRIDE (KANAN) -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- KOLOM KIRI (LEBAR 8/12): KALENDER VISUAL BULANAN INTERAKTIF -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-6">
            
            <!-- Header Kalender: Navigasi Bulan & Tombol Cepat -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <h3 class="text-base sm:text-lg font-bold text-[#015987] tracking-tight">
                        {{ $currentMonthDate->translatedFormat('F Y') }}
                    </h3>
                    <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">
                        Asia/Jakarta (WIB)
                    </span>
                </div>

                <!-- Tombol Navigasi -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.calendar.index', ['year' => $prevMonthDate->year, 'month' => $prevMonthDate->month]) }}" 
                       class="p-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs"
                       title="Bulan Sebelumnya ({{ $prevMonthDate->translatedFormat('F Y') }})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>

                    <a href="{{ route('admin.calendar.index') }}" 
                       class="px-3.5 py-2 text-xs font-semibold text-[#015987] bg-sky-50 border border-sky-200/70 hover:bg-sky-100 rounded-xl transition shadow-2xs">
                        Bulan Ini
                    </a>

                    <a href="{{ route('admin.calendar.index', ['year' => $nextMonthDate->year, 'month' => $nextMonthDate->month]) }}" 
                       class="p-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs"
                       title="Bulan Berikutnya ({{ $nextMonthDate->translatedFormat('F Y') }})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <!-- Tombol Buka Modal Tambah -->
                    <button type="button" 
                            onclick="openCalendarModal('{{ $currentMonthDate->copy()->startOfMonth()->format('Y-m-d') }}', 'holiday', '', '', '')"
                            class="ml-2 px-3.5 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Atur Tanggal</span>
                    </button>
                </div>
            </div>

            <!-- Petunjuk Indikator Warna (Legend) -->
            <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-[11px] text-slate-500 pt-1">
                <span class="flex items-center gap-1.5 font-medium">
                    <span class="w-3 h-3 rounded-md bg-white border border-slate-200"></span>
                    Hari Kerja Normal
                </span>
                <span class="flex items-center gap-1.5 font-medium">
                    <span class="w-3 h-3 rounded-md bg-slate-100 border border-slate-200"></span>
                    Akhir Pekan (Sabtu/Minggu)
                </span>
                <span class="flex items-center gap-1.5 font-semibold text-rose-600">
                    <span class="w-3 h-3 rounded-md bg-rose-100 border border-rose-300"></span>
                    Hari Libur / Tanggal Merah
                </span>
                <span class="flex items-center gap-1.5 font-semibold text-teal-600">
                    <span class="w-3 h-3 rounded-md bg-teal-100 border border-teal-300"></span>
                    Hari Kerja Khusus (Override)
                </span>
                <span class="flex items-center gap-1.5 font-semibold text-[#015987]">
                    <span class="w-3 h-3 rounded-md ring-2 ring-[#015987] bg-sky-50"></span>
                    Hari Ini
                </span>
            </div>

            <!-- Grid Kalender Bulanan (7 Kolom: Senin s/d Minggu) -->
            <div class="overflow-x-auto">
                <div class="min-w-[620px]">
                    <!-- Header Nama Hari -->
                    <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold uppercase tracking-wider text-slate-400 pb-2">
                        <div class="py-1">Senin</div>
                        <div class="py-1">Selasa</div>
                        <div class="py-1">Rabu</div>
                        <div class="py-1">Kamis</div>
                        <div class="py-1">Jumat</div>
                        <div class="py-1 text-rose-400">Sabtu</div>
                        <div class="py-1 text-rose-500">Minggu</div>
                    </div>

                    <!-- Baris-Baris Tanggal -->
                    <div class="space-y-2">
                        @foreach($weeks as $week)
                            <div class="grid grid-cols-7 gap-2">
                                @foreach($week as $day)
                                    @php
                                        $isCurrentMonth = $day['is_current_month'];
                                        $isToday = $day['is_today'];
                                        $isWeekend = $day['is_weekend'];
                                        $status = $day['status'];
                                        $hasException = ! is_null($day['exception']);
                                        $exception = $day['exception'];

                                        // Styling latar belakang & border cell
                                        $cellClass = 'bg-white border-slate-200/80 text-slate-700 hover:border-[#015987]';
                                        if (! $isCurrentMonth) {
                                            $cellClass = 'bg-slate-50/50 border-slate-100 text-slate-300 hover:border-slate-200';
                                        } elseif ($hasException && $exception->isHoliday()) {
                                            $cellClass = 'bg-rose-50/80 border-rose-200 text-rose-800 hover:border-rose-400';
                                        } elseif ($hasException && $exception->isWorkingDay()) {
                                            $cellClass = 'bg-teal-50/80 border-teal-200 text-teal-800 hover:border-teal-400';
                                        } elseif ($isWeekend) {
                                            $cellClass = 'bg-slate-50/80 border-slate-200/70 text-slate-500 hover:border-slate-300';
                                        }

                                        if ($isToday) {
                                            $cellClass .= ' ring-2 ring-[#015987] ring-offset-1 font-bold';
                                        }
                                    @endphp

                                    <div 
                                        onclick="openCalendarModal(
                                            '{{ $day['date'] }}', 
                                            '{{ $hasException ? $exception->type : ($isWeekend ? 'working_day' : 'holiday') }}', 
                                            '{{ $hasException ? addslashes($exception->name) : '' }}', 
                                            '{{ $hasException ? addslashes($exception->description ?? '') : '' }}', 
                                            '{{ $hasException ? route('admin.calendar.destroy', $exception) : '' }}'
                                        )"
                                        class="min-h-[92px] p-2.5 rounded-xl border {{ $cellClass }} flex flex-col justify-between cursor-pointer transition shadow-2xs hover:shadow-xs group relative select-none">
                                        
                                        <!-- Header Cell: Nomor Hari & Badge Hari Ini -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold {{ $isToday ? 'text-[#015987] font-extrabold' : '' }}">
                                                {{ $day['day'] }}
                                            </span>

                                            @if($isToday)
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#015987]"></span>
                                            @endif
                                        </div>

                                        <!-- Informasi Status / Label Override -->
                                        <div class="mt-1 space-y-1">
                                            @if($hasException)
                                                @if($exception->isHoliday())
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-500 text-white tracking-wide uppercase leading-tight">
                                                        Libur
                                                    </span>
                                                    <p class="text-[10px] font-semibold text-rose-700 truncate leading-snug" title="{{ $exception->name }}">
                                                        {{ $exception->name }}
                                                    </p>
                                                @else
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-teal-600 text-white tracking-wide uppercase leading-tight">
                                                        Kerja Khusus
                                                    </span>
                                                    <p class="text-[10px] font-semibold text-teal-700 truncate leading-snug" title="{{ $exception->name }}">
                                                        {{ $exception->name }}
                                                    </p>
                                                @endif
                                            @elseif($isWeekend && $isCurrentMonth)
                                                <span class="text-[9px] font-medium text-slate-400 block">
                                                    Akhir Pekan
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Tooltip hover tanda klik -->
                                        <span class="opacity-0 group-hover:opacity-100 transition absolute bottom-1 right-2 text-[9px] text-[#015987] font-bold">
                                            Atur &rsaquo;
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Footer Keterangan Interaksi -->
            <div class="pt-3 border-t border-slate-100 text-xs text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <span>Klik langsung pada tanggal kalender mana pun untuk mengatur atau membatalkan hari libur/kerja.</span>
                <span class="font-semibold text-sky-700">Waktu Server: {{ \Carbon\Carbon::now(config('wantrace.timezone', 'Asia/Jakarta'))->translatedFormat('H:i') }} WIB</span>
            </div>

        </div>

        <!-- KOLOM KANAN (LEBAR 4/12): DAFTAR OVERRIDE BULAN INI & ATURAN HIERARKI -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- KARTU 1: DAFTAR PENGECUALIAN BULAN AKTIF -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="text-sm font-bold text-[#015987]">Pengecualian Bulan Ini</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $currentMonthDate->translatedFormat('F Y') }}</p>
                    </div>
                    <span class="text-xs font-bold text-[#015987] bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-200/70">
                        {{ $monthlyExceptions->count() }} Data
                    </span>
                </div>

                @if($monthlyExceptions->isEmpty())
                    <div class="text-center py-8 px-4 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-600">Belum Ada Pengecualian</p>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Bulan ini berjalan dengan aturan standar operasional kantor (Senin–Jumat kerja, Sabtu–Minggu libur).
                        </p>
                    </div>
                @else
                    <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                        @foreach($monthlyExceptions as $item)
                            <div class="p-3.5 rounded-xl border {{ $item->isHoliday() ? 'border-rose-100 bg-rose-50/40' : 'border-teal-100 bg-teal-50/40' }} space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $item->isHoliday() ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-teal-100 text-teal-700 border border-teal-200' }}">
                                            {{ $item->isHoliday() ? 'HARI LIBUR' : 'HARI KERJA KHUSUS' }}
                                        </span>
                                        <h5 class="text-xs font-bold text-slate-900 mt-1 leading-snug">
                                            {{ $item->name }}
                                        </h5>
                                    </div>

                                    <!-- Tombol Aksi Hapus -->
                                    <form method="POST" action="{{ route('admin.calendar.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengecualian tanggal {{ $item->exception_date->translatedFormat('d F Y') }}? Data presensi historis tidak akan terpengaruh.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-white transition" title="Hapus Override">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t {{ $item->isHoliday() ? 'border-rose-100' : 'border-teal-100' }}">
                                    <span class="font-medium text-slate-700">
                                        {{ $item->exception_date->translatedFormat('l, d F Y') }}
                                    </span>
                                    <button type="button" 
                                            onclick="openCalendarModal(
                                                '{{ $item->exception_date->format('Y-m-d') }}', 
                                                '{{ $item->type }}', 
                                                '{{ addslashes($item->name) }}', 
                                                '{{ addslashes($item->description ?? '') }}', 
                                                '{{ route('admin.calendar.destroy', $item) }}'
                                            )"
                                            class="text-[11px] font-semibold text-[#015987] hover:underline">
                                        Edit
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

            <!-- KARTU 2: ATURAN HIERARKI KALENDER PT WAN TEKNOLOGI -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-3.5">
                <div class="flex items-center gap-2 text-xs font-bold text-[#015987] pb-2 border-b border-slate-100">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Hierarki Kalender Presensi</span>
                </div>

                <ul class="text-xs text-slate-600 space-y-2.5 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-sky-100 text-[#015987] text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                        <span><strong>Override Kalender (Prioritas Utama):</strong> Hari libur khusus / hari kerja pengganti yang ditetapkan di sini akan mendahului aturan hari biasa.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-sky-100 text-[#015987] text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                        <span><strong>Default Mingguan:</strong> Jika tidak ada override, Senin s.d. Jumat adalah hari kerja normal, sedangkan Sabtu &amp; Minggu adalah libur otomatis.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-4 h-4 rounded-full bg-sky-100 text-[#015987] text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                        <span><strong>Integritas Presensi Historis:</strong> Menambah, mengubah, atau menghapus kalender tidak akan mengubah atau menghapus data presensi yang sudah tercatat.</span>
                    </li>
                </ul>
            </div>

        </div>

    </div>

</div>

<!-- ============================================================== -->
<!-- 3. MODAL POP-UP PENETAPAN PENGECUALIAN KALENDER                -->
<!-- ============================================================== -->
<div id="calendarModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl border border-slate-200/80 shadow-xl overflow-hidden transition-all transform scale-100">
        
        <!-- Header Modal -->
        <div class="px-6 py-5 bg-gradient-to-r from-sky-50 to-indigo-50/50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-[#015987] text-white flex items-center justify-center flex-shrink-0 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-sm font-bold text-[#015987]">Atur Pengecualian Kalender</h3>
                    <p class="text-[11px] text-slate-500" id="modalSubtitle">Pilih jenis pengecualian untuk tanggal tersebut</p>
                </div>
            </div>

            <button type="button" onclick="closeCalendarModal()" class="text-slate-400 hover:text-slate-700 transition p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form Simpan / Update Pengecualian -->
        <form id="calendarForm" method="POST" action="{{ route('admin.calendar.store') }}" class="p-6 space-y-5">
            @csrf
            
            <!-- 1. Pilihan Tanggal -->
            <div class="space-y-1.5">
                <label for="modalExceptionDate" class="block text-xs font-bold text-slate-700">
                    Tanggal Pengecualian <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="modalExceptionDate" 
                    name="exception_date" 
                    required 
                    class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs"
                >
            </div>

            <!-- 2. Pilihan Jenis Override (Holiday vs Working Day) -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">
                    Tipe Hari Operasional <span class="text-rose-500">*</span>
                </label>
                
                <div class="grid grid-cols-2 gap-3">
                    <!-- Opsi Holiday -->
                    <label class="cursor-pointer">
                        <input type="radio" name="type" id="typeHoliday" value="holiday" checked class="peer sr-only">
                        <div class="p-3.5 rounded-xl border border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50/60 transition text-center space-y-1 hover:border-slate-300">
                            <span class="inline-flex w-7 h-7 rounded-full bg-rose-100 text-rose-600 items-center justify-center mx-auto mb-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <p class="text-xs font-bold text-slate-800">Hari Libur</p>
                            <p class="text-[10px] text-slate-400">Presensi ditiadakan</p>
                        </div>
                    </label>

                    <!-- Opsi Working Day -->
                    <label class="cursor-pointer">
                        <input type="radio" name="type" id="typeWorkingDay" value="working_day" class="peer sr-only">
                        <div class="p-3.5 rounded-xl border border-slate-200 peer-checked:border-teal-500 peer-checked:bg-teal-50/60 transition text-center space-y-1 hover:border-slate-300">
                            <span class="inline-flex w-7 h-7 rounded-full bg-teal-100 text-teal-600 items-center justify-center mx-auto mb-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <p class="text-xs font-bold text-slate-800">Hari Kerja Khusus</p>
                            <p class="text-[10px] text-slate-400">Presensi wajib aktif</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 3. Nama / Alasan -->
            <div class="space-y-1.5">
                <label for="modalName" class="block text-xs font-bold text-slate-700">
                    Nama / Alasan Pengecualian <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="modalName" 
                    name="name" 
                    maxlength="150"
                    placeholder="Contoh: Libur Idul Fitri, Cuti Bersama, atau Pelatihan Akhir Pekan" 
                    required 
                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs"
                >
            </div>

            <!-- 4. Keterangan Tambahan (Opsional) -->
            <div class="space-y-1.5">
                <label for="modalDescription" class="block text-xs font-bold text-slate-700">
                    Keterangan Tambahan <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea 
                    id="modalDescription" 
                    name="description" 
                    rows="3" 
                    maxlength="1000"
                    placeholder="Tuliskan catatan detail jika diperlukan..." 
                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#015987]/20 focus:border-[#015987] transition shadow-2xs resize-none"
                ></textarea>
            </div>

            <!-- Tombol Aksi Modal -->
            <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100">
                <div id="deleteActionContainer">
                    <!-- Tombol Hapus disuntik via Javascript jika tanggal sudah memiliki pengecualian -->
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    <button type="button" onclick="closeCalendarModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#015987] hover:bg-[#01486e] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        Simpan Pengecualian
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<!-- Form tersembunyi untuk menghapus pengecualian kalender dari dalam modal -->
<form id="modalDeleteForm" method="POST" action="" class="hidden">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    function openCalendarModal(date, type, name, description, deleteUrl) {
        const modal = document.getElementById('calendarModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const dateInput = document.getElementById('modalExceptionDate');
        const nameInput = document.getElementById('modalName');
        const descInput = document.getElementById('modalDescription');
        const typeHoliday = document.getElementById('typeHoliday');
        const typeWorkingDay = document.getElementById('typeWorkingDay');
        const deleteContainer = document.getElementById('deleteActionContainer');
        const deleteForm = document.getElementById('modalDeleteForm');

        dateInput.value = date;
        nameInput.value = name || '';
        descInput.value = description || '';

        if (type === 'working_day') {
            typeWorkingDay.checked = true;
        } else {
            typeHoliday.checked = true;
        }

        if (deleteUrl) {
            modalTitle.innerText = 'Edit Pengecualian Tanggal';
            modalSubtitle.innerText = 'Perbarui status tanggal atau hapus override untuk kembali ke default';
            deleteForm.action = deleteUrl;
            deleteContainer.innerHTML = `
                <button type="button" onclick="confirmDeleteOverride()" class="px-3 py-2 text-xs font-semibold text-rose-500 hover:bg-rose-50 border border-rose-200 rounded-xl transition">
                    Hapus Override
                </button>
            `;
        } else {
            modalTitle.innerText = 'Atur Pengecualian Kalender';
            modalSubtitle.innerText = 'Tetapkan hari libur atau hari kerja khusus untuk tanggal ' + date;
            deleteContainer.innerHTML = '';
        }

        modal.classList.remove('hidden');
    }

    function closeCalendarModal() {
        document.getElementById('calendarModal').classList.add('hidden');
    }

    function confirmDeleteOverride() {
        if (confirm('Apakah Anda yakin ingin menghapus pengecualian pada tanggal ini? Aturan akan kembali ke default operasional (Senin-Jumat kerja, Sabtu-Minggu libur).')) {
            document.getElementById('modalDeleteForm').submit();
        }
    }

    // Menutup modal dengan tombol ESC atau klik di luar modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCalendarModal();
        }
    });

    document.getElementById('calendarModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCalendarModal();
        }
    });
</script>
@endpush
