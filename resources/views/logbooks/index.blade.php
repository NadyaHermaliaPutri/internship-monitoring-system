@extends('layouts.app')

@section('title', 'Logbook Harian')
@section('page-title', 'Logbook Aktivitas Harian')
@section('page-subtitle', auth()->user()->isIntern()
    ? 'Catat aktivitas harian Anda dan pantau status verifikasi dari PIC Pembimbing'
    : (auth()->user()->isPic()
        ? 'Pantau logbook harian peserta magang yang berada dalam cakupan penugasan Anda'
        : 'Monitoring seluruh logbook harian peserta magang PT WAN Teknologi Internasional'))

@section('content')
@php
    $user = auth()->user();
    $rolePrefix = $user->role;
    $hasActiveFilter = request()->filled('search')
        || request()->filled('source')
        || request()->filled('status')
        || request()->filled('date')
        || request()->filled('intern_id');
@endphp

<div class="space-y-6">

    {{-- Flash Message --}}
    @if (session('status'))
        <div class="flex items-center justify-between gap-3 px-4 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    {{-- 5 Kartu Ringkasan KPI Logbook --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Logbook Manual</p>
            <p class="text-2xl font-extrabold text-[#015987] mt-1.5">{{ $summary['total_manual'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Diisi oleh peserta</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Menunggu Review</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1.5">{{ $summary['pending'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Status Pending</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Disetujui PIC</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1.5">{{ $summary['approved'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Terkunci & Terverifikasi</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Perlu Revisi</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-1.5">{{ $summary['revision_requested'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Menunggu perbaikan</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs col-span-2 sm:col-span-1">
            <p class="text-[11px] font-semibold text-sky-600 uppercase tracking-wider">Catatan Penugasan</p>
            <p class="text-2xl font-extrabold text-sky-700 mt-1.5">{{ $summary['task_assignments'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Otomatis dari PIC</p>
        </div>
    </div>

    {{-- Kontainer Full-Width: Header Aksi, Bar Filter, Tabel Desktop & Kartu Mobile --}}
    <div class="w-full bg-white rounded-2xl border border-slate-200/70 shadow-xs p-5 sm:p-6 space-y-5">

        {{-- Header Daftar Riwayat + Tombol + Tambah Logbook --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-[#015987]">Riwayat Logbook Harian</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan catatan aktivitas manual peserta dan catatan otomatis penugasan PIC.
                </p>
            </div>

            @if ($user->isIntern())
                <a
                    href="{{ route('intern.logbooks.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs shadow-xs transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Tambah Logbook</span>
                </a>
            @endif
        </div>

        {{-- Bar Filter & Pencarian Kata Kunci --}}
        <form method="GET" action="{{ route($rolePrefix . '.logbooks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 bg-slate-50/70 p-4 rounded-xl border border-slate-100 items-end">
            {{-- Pencarian Kata Kunci --}}
            <div class="{{ $user->isIntern() ? 'lg:col-span-4' : 'lg:col-span-3' }}">
                <label for="search" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kata Kunci</label>
                <div class="relative">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari judul atau uraian..."
                        class="w-full pl-8 pr-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987]"
                    >
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Filter Peserta (Khusus PIC & Admin) --}}
            @if (! $user->isIntern())
                <div class="lg:col-span-2">
                    <label for="intern_id" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Peserta</label>
                    <select id="intern_id" name="intern_id" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                        <option value="">Semua Peserta</option>
                        @foreach ($internOptions as $internOption)
                            <option value="{{ $internOption->id }}" @selected((string) request('intern_id') === (string) $internOption->id)>
                                {{ $internOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Filter Sumber --}}
            <div class="lg:col-span-2">
                <label for="source" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Sumber</label>
                <select id="source" name="source" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                    <option value="">Semua Sumber</option>
                    <option value="manual" @selected(request('source') === 'manual')>Manual Peserta</option>
                    <option value="task_assignment" @selected(request('source') === 'task_assignment')>Otomatis Penugasan PIC</option>
                </select>
            </div>

            {{-- Filter Status --}}
            <div class="lg:col-span-2">
                <label for="status" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status</label>
                <select id="status" name="status" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="revision_requested" @selected(request('status') === 'revision_requested')>Perlu Revisi</option>
                </select>
            </div>

            {{-- Filter Tanggal & Tombol Aksi --}}
            <div class="{{ $user->isIntern() ? 'lg:col-span-4' : 'lg:col-span-3' }}">
                <label for="date" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                <div class="flex items-center gap-2">
                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="{{ request('date') }}"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700"
                    >
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-[#015987] text-white text-xs font-bold hover:bg-[#014467] transition shrink-0">
                        Filter
                    </button>
                    @if ($hasActiveFilter)
                        <a
                            href="{{ route($rolePrefix . '.logbooks.index') }}"
                            class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 text-xs font-semibold transition shrink-0"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Daftar Riwayat Logbook --}}
        @if ($logbooks->isEmpty())
            <div class="text-center py-12 px-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/40 space-y-2">
                <p class="text-sm font-bold text-slate-700">Belum ada catatan logbook</p>
                <p class="text-xs text-slate-500">
                    @if ($hasActiveFilter)
                        Tidak ada catatan logbook yang cocok dengan filter pencarian Anda.
                    @elseif ($user->isIntern())
                        Silakan catat aktivitas harian Anda dengan menekan tombol <strong>+ Tambah Logbook</strong> di kanan atas.
                    @else
                        Belum ada logbook peserta dalam cakupan penugasan Anda.
                    @endif
                </p>
            </div>
        @else
            {{-- TAMPILAN DESKTOP (md ke atas): Tabel Ringkas Full-Width --}}
            <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200/80">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#015987]/5 border-b border-slate-200/80 text-[11px] font-bold text-[#015987] uppercase tracking-wider">
                            <th class="py-3.5 px-4 whitespace-nowrap">Tanggal</th>
                            @if (! $user->isIntern())
                                <th class="py-3.5 px-4 whitespace-nowrap">Peserta</th>
                            @endif
                            <th class="py-3.5 px-4 whitespace-nowrap">Sumber</th>
                            <th class="py-3.5 px-4 w-full">Judul &amp; Ringkasan</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Status</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach ($logbooks as $item)
                            <tr class="hover:bg-slate-50/80 transition align-middle">
                                {{-- Tanggal --}}
                                <td class="py-3.5 px-4 whitespace-nowrap font-bold text-[#015987]">
                                    {{ $item->log_date->translatedFormat('d M Y') }}
                                </td>

                                {{-- Peserta (PIC & Admin) --}}
                                @if (! $user->isIntern())
                                    <td class="py-3.5 px-4 whitespace-nowrap font-semibold text-slate-800">
                                        {{ $item->intern?->name }}
                                    </td>
                                @endif

                                {{-- Sumber --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($item->isTaskAssignment())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                            Otomatis Penugasan PIC
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/70">
                                            Manual Peserta
                                        </span>
                                    @endif
                                </td>

                                {{-- Judul & Ringkasan 1 Baris (line-clamp-1) --}}
                                <td class="py-3.5 px-4 max-w-md">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 truncate">{{ $item->title }}</span>
                                        @if ($item->revisions_count > 0)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0">
                                                {{ $item->revisions_count }}x Revisi
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                                        {{ $item->activity_description }}
                                    </p>
                                </td>

                                {{-- Status --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($item->isManual() && $item->status)
                                        @php
                                            $statusClasses = match ($item->status->value) {
                                                'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'revision_requested' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default => 'bg-amber-50 text-amber-700 border-amber-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClasses }}">
                                            {{ $item->status->label() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50/70 text-sky-700 border border-sky-200/70">
                                            Tercatat Otomatis
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route($rolePrefix . '.logbooks.show', $item) }}"
                                            class="px-3 py-1.5 rounded-xl border border-slate-200 hover:border-[#015987] text-xs font-semibold text-[#015987] transition"
                                        >
                                            Detail
                                        </a>

                                        @can('update', $item)
                                            <a
                                                href="{{ route('intern.logbooks.edit', $item) }}"
                                                class="px-3 py-1.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-semibold text-white transition"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAMPILAN MOBILE / PWA (< md): Daftar Kartu Ringkas 1 Kolom dengan line-clamp-2 --}}
            <div class="md:hidden space-y-3">
                @foreach ($logbooks as $item)
                    <div class="p-4 rounded-2xl border border-slate-200/80 bg-white space-y-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-bold text-[#015987]">
                                {{ $item->log_date->translatedFormat('d M Y') }}
                                @if (! $user->isIntern())
                                    <span class="text-slate-600 font-semibold">• {{ $item->intern?->name }}</span>
                                @endif
                            </span>

                            @if ($item->isManual() && $item->status)
                                @php
                                    $statusClasses = match ($item->status->value) {
                                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'revision_requested' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClasses }}">
                                    {{ $item->status->label() }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                    Tercatat Otomatis
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            @if ($item->isTaskAssignment())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    Otomatis Penugasan PIC
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    Manual Peserta
                                </span>
                            @endif

                            @if ($item->revisions_count > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $item->revisions_count }}x Revisi
                                </span>
                            @endif
                        </div>

                        <h3 class="text-sm font-bold text-slate-900">{{ $item->title }}</h3>

                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                            {{ $item->activity_description }}
                        </p>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                            <a
                                href="{{ route($rolePrefix . '.logbooks.show', $item) }}"
                                class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-[#015987]"
                            >
                                Detail
                            </a>

                            @can('update', $item)
                                <a
                                    href="{{ route('intern.logbooks.edit', $item) }}"
                                    class="px-3 py-1.5 rounded-xl bg-[#015987] text-xs font-semibold text-white"
                                >
                                    Edit
                                </a>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-3">
                {{ $logbooks->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
