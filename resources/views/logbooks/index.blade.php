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

    {{-- Ringkasan Metrik Logbook --}}
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

    <div class="grid grid-cols-1 {{ $user->isIntern() ? 'xl:grid-cols-12' : '' }} gap-6 items-start">

        {{-- KOLOM KIRI (KHUSUS PESERTA): Form Input Logbook Harian --}}
        @if ($user->isIntern())
            <div class="xl:col-span-5 bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-[#015987]">Input Logbook Harian</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Anda dapat menambahkan lebih dari 1 aktivitas per hari (maks. mundur {{ $maxBackdateDays }} hari).
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-[#015987] border border-sky-200">
                        WIB
                    </span>
                </div>

                <form method="POST" action="{{ route('intern.logbooks.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="log_date" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Tanggal Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="log_date"
                            name="log_date"
                            value="{{ old('log_date', $todayDate) }}"
                            min="{{ $earliestDate }}"
                            max="{{ $todayDate }}"
                            required
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('log_date') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">
                            Rentang diizinkan: {{ \Carbon\Carbon::parse($earliestDate)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($todayDate)->translatedFormat('d M Y') }}
                        </p>
                        @error('log_date')
                            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Judul Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            maxlength="150"
                            placeholder="Contoh: Implementasi validasi form presensi harian"
                            required
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('title') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                        >
                        @error('title')
                            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="activity_description" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Uraian Aktivitas <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="activity_description"
                            name="activity_description"
                            rows="4"
                            placeholder="Jelaskan secara rinci kegiatan yang Anda kerjakan (minimal 10 karakter)..."
                            required
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('activity_description') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                        >{{ old('activity_description') }}</textarea>
                        @error('activity_description')
                            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="result" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Hasil / Output Kegiatan <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea
                            id="result"
                            name="result"
                            rows="2"
                            placeholder="Contoh: PR #12 siap ditinjau / modul selesai diuji lokal"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('result') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                        >{{ old('result') }}</textarea>
                        @error('result')
                            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button
                            type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs shadow-xs transition"
                        >
                            Simpan Logbook
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- KOLOM KANAN: Filter & Daftar Riwayat Logbook --}}
        <div class="{{ $user->isIntern() ? 'xl:col-span-7' : '' }} bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-[#015987]">Riwayat Logbook</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Menampilkan catatan aktivitas manual peserta dan catatan otomatis penugasan PIC.
                    </p>
                </div>

                @if ($user->isIntern())
                    <a
                        href="{{ route('intern.logbooks.create') }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl border border-[#015987] text-[#015987] hover:bg-sky-50 font-bold text-xs transition"
                    >
                        + Form Halaman Penuh
                    </a>
                @endif
            </div>

            {{-- Bar Filter --}}
            <form method="GET" action="{{ route($rolePrefix . '.logbooks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
                @if (! $user->isIntern())
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Peserta Magang</label>
                        <select name="intern_id" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                            <option value="">Semua Peserta</option>
                            @foreach ($internOptions as $internOption)
                                <option value="{{ $internOption->id }}" @selected((string) request('intern_id') === (string) $internOption->id)>
                                    {{ $internOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Sumber Catatan</label>
                    <select name="source" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                        <option value="">Semua Sumber</option>
                        <option value="manual" @selected(request('source') === 'manual')>Input Manual Peserta</option>
                        <option value="task_assignment" @selected(request('source') === 'task_assignment')>Penugasan PIC (Otomatis)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Review</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Menunggu Review</option>
                        <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                        <option value="revision_requested" @selected(request('status') === 'revision_requested')>Perlu Revisi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="date"
                            name="date"
                            value="{{ request('date') }}"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700"
                        >
                        <button type="submit" class="px-3 py-2 rounded-lg bg-[#015987] text-white text-xs font-bold hover:bg-[#014467] transition">
                            Filter
                        </button>
                    </div>
                </div>
            </form>

            {{-- Daftar Entri Logbook --}}
            @if ($logbooks->isEmpty())
                <div class="text-center py-12 px-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/40">
                    <p class="text-sm font-bold text-slate-700">Belum ada catatan logbook</p>
                    <p class="text-xs text-slate-500 mt-1">
                        @if ($user->isIntern())
                            Silakan isi aktivitas harian Anda melalui formulir di samping.
                        @else
                            Belum ada logbook yang sesuai dengan filter atau cakupan penugasan Anda.
                        @endif
                    </p>
                </div>
            @else
                <div class="space-y-3.5">
                    @foreach ($logbooks as $item)
                        <div class="p-4 rounded-2xl border border-slate-200/80 hover:border-slate-300 bg-white transition space-y-3">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-bold text-[#015987]">
                                            {{ $item->log_date->translatedFormat('d M Y') }}
                                        </span>

                                        @if (! $user->isIntern())
                                            <span class="text-xs font-semibold text-slate-700">
                                                • {{ $item->intern?->name }}
                                            </span>
                                        @endif

                                        {{-- Badge Sumber --}}
                                        @if ($item->isTaskAssignment())
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                                Otomatis Penugasan PIC
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                                Manual Peserta
                                            </span>
                                        @endif

                                        {{-- Badge Status (Hanya untuk Manual) --}}
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
                                        @endif

                                        @if ($item->revisions_count > 0)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                {{ $item->revisions_count }}x Revisi
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="text-sm font-bold text-slate-900">
                                        {{ $item->title }}
                                    </h3>
                                </div>

                                <div class="flex items-center gap-2">
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
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">
                                {{ $item->activity_description }}
                            </p>

                            @if ($item->result)
                                <div class="text-[11px] text-slate-600 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
                                    <span class="font-bold text-slate-700">Hasil:</span> {{ $item->result }}
                                </div>
                            @endif

                            @if ($item->review_note)
                                <div class="text-[11px] text-rose-700 bg-rose-50/70 px-3 py-2 rounded-xl border border-rose-200/70">
                                    <span class="font-bold">Catatan PIC ({{ $item->reviewer?->name ?? 'PIC' }}):</span>
                                    {{ $item->review_note }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="pt-3">
                    {{ $logbooks->links() }}
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
