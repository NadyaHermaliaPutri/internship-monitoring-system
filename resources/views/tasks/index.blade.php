@extends('layouts.app')

@section('title', 'Tugas Magang')
@section('page-title', 'Penugasan & Pemantauan Tugas')
@section('page-subtitle', auth()->user()->isPic()
    ? 'Kelola pemberian tugas, batas waktu (deadline), dan pantau progres pengerjaan peserta bimbingan Anda'
    : (auth()->user()->isIntern()
        ? 'Daftar tugas yang diberikan oleh PIC Pembimbing Anda'
        : 'Monitoring seluruh penugasan peserta magang PT WAN Teknologi Internasional'))

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

    {{-- Ringkasan Metrik Tugas --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Tugas</p>
            <p class="text-2xl font-extrabold text-[#015987] mt-1.5">{{ $summary['total'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Semua status</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-sky-600 uppercase tracking-wider">Aktif Dikerjakan</p>
            <p class="text-2xl font-extrabold text-sky-700 mt-1.5">{{ $summary['assigned'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Ditugaskan / Progres</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Menunggu Review</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1.5">{{ $summary['awaiting_review'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Dikumpulkan peserta</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider">Perlu Revisi</p>
            <p class="text-2xl font-extrabold text-indigo-600 mt-1.5">{{ $summary['revision_requested'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Menunggu perbaikan</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Diterima (Accepted)</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1.5">{{ $summary['accepted'] }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Tugas selesai</p>
        </div>

        <div class="bg-white rounded-2xl border border-rose-200/80 p-4 shadow-xs">
            <p class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Lewat Deadline</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-1.5">{{ $summary['overdue'] }}</p>
            <p class="text-[11px] text-rose-400 mt-1">Indikator Overdue</p>
        </div>
    </div>

    <div class="grid grid-cols-1 {{ $user->isPic() ? 'xl:grid-cols-12' : '' }} gap-6 items-start">

        {{-- KOLOM KIRI (KHUSUS PIC): Form Cepat Buat Tugas Baru --}}
        @if ($user->isPic())
            <div class="xl:col-span-5 bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-[#015987]">Buat Tugas Baru</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Otomatis mencatat peristiwa penugasan ke Logbook peserta terkait.
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-[#015987] border border-sky-200">
                        WIB
                    </span>
                </div>

                @if ($internOptions->isEmpty())
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs space-y-1">
                        <p class="font-bold">Belum Ada Peserta Aktif dalam Penugasan Anda</p>
                        <p class="text-[11px]">
                            Anda hanya dapat memberikan tugas kepada peserta magang aktif yang sedang ditugaskan kepada Anda pada tabel penugasan PIC.
                        </p>
                    </div>
                @else
                    <form method="POST" action="{{ route('pic.tasks.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="intern_user_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Peserta Magang Penerima <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="intern_user_id"
                                name="intern_user_id"
                                required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('intern_user_id') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                            >
                                <option value="">-- Pilih Peserta Bimbingan Aktif --</option>
                                @foreach ($internOptions as $internOption)
                                    <option value="{{ $internOption->id }}" @selected((string) old('intern_user_id') === (string) $internOption->id)>
                                        {{ $internOption->name }} ({{ $internOption->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('intern_user_id')
                                <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="title" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Judul Tugas <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                maxlength="180"
                                placeholder="Contoh: Slicing UI Halaman Laporan Evaluasi Magang"
                                required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('title') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                            >
                            @error('title')
                                <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="deadline_at" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Batas Waktu Pengumpulan (Deadline WIB) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="datetime-local"
                                id="deadline_at"
                                name="deadline_at"
                                value="{{ old('deadline_at', $defaultDeadline) }}"
                                required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('deadline_at') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                            >
                            <p class="text-[11px] text-slate-400 mt-1">
                                Wajib berada di masa depan sesuai zona waktu Asia/Jakarta (WIB).
                            </p>
                            @error('deadline_at')
                                <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Deskripsi & Spesifikasi Tugas <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Jelaskan instruksi pengerjaan, kriteria selesai, dan format bukti yang diharapkan..."
                                required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('description') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-2 flex items-center justify-end">
                            <button
                                type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs shadow-xs transition"
                            >
                                Tugaskan kepada Peserta
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif

        {{-- KOLOM KANAN: Filter & Daftar Tugas --}}
        <div class="{{ $user->isPic() ? 'xl:col-span-7' : '' }} bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 space-y-5">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-[#015987]">Daftar Tugas</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Indikator Overdue dihitung otomatis dari batas waktu (deadline) terbaru.
                    </p>
                </div>

                @if ($user->isPic())
                    <a
                        href="{{ route('pic.tasks.create') }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl border border-[#015987] text-[#015987] hover:bg-sky-50 font-bold text-xs transition"
                    >
                        + Form Halaman Penuh
                    </a>
                @endif
            </div>

            {{-- Bar Filter --}}
            <form method="GET" action="{{ route($rolePrefix . '.tasks.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
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
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Status Tugas</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                        <option value="">Semua Status</option>
                        @foreach ($statusOptions as $statusCase)
                            <option value="{{ $statusCase->value }}" @selected(request('status') === $statusCase->value)>
                                {{ $statusCase->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kondisi Deadline</label>
                    <div class="flex items-center gap-2">
                        <select name="overdue" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white text-slate-700">
                            <option value="">Semua</option>
                            <option value="1" @selected(request('overdue') === '1')>Hanya yang Overdue</option>
                        </select>
                        <button type="submit" class="px-3.5 py-2 rounded-lg bg-[#015987] text-white text-xs font-bold hover:bg-[#014467] transition">
                            Filter
                        </button>
                    </div>
                </div>
            </form>

            {{-- Daftar Entri Tugas --}}
            @if ($tasks->isEmpty())
                <div class="text-center py-12 px-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/40">
                    <p class="text-sm font-bold text-slate-700">Belum ada data tugas</p>
                    <p class="text-xs text-slate-500 mt-1">
                        @if ($user->isPic())
                            Silakan buat tugas baru untuk peserta bimbingan Anda pada formulir di samping.
                        @elseif ($user->isIntern())
                            Belum ada tugas yang diberikan oleh PIC Pembimbing Anda.
                        @else
                            Belum ada tugas yang sesuai dengan filter pencarian.
                        @endif
                    </p>
                </div>
            @else
                <div class="space-y-3.5">
                    @foreach ($tasks as $taskItem)
                        @php
                            $isOverdue = $taskItem->isOverdue();
                            $statusBadge = match ($taskItem->status->value) {
                                'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'submitted', 'resubmitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'revision_requested' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp

                        <div class="p-4 rounded-2xl border {{ $isOverdue ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80 bg-white' }} hover:border-slate-300 transition space-y-3">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                            {{ $taskItem->status->label() }}
                                        </span>

                                        @if ($isOverdue)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-300">
                                                OVERDUE (Lewat Deadline)
                                            </span>
                                        @endif

                                        <span class="text-[11px] text-slate-500">
                                            Ditugaskan: {{ $taskItem->assigned_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                                        </span>
                                    </div>

                                    <h3 class="text-sm font-bold text-slate-900">
                                        {{ $taskItem->title }}
                                    </h3>

                                    <p class="text-[11px] text-slate-500">
                                        @if (! $user->isIntern())
                                            Peserta: <strong class="text-slate-700">{{ $taskItem->intern?->name }}</strong> •
                                        @endif
                                        PIC: <strong class="text-slate-700">{{ $taskItem->pic?->name }}</strong>
                                        @if ($taskItem->deadline_at)
                                            • Deadline:
                                            <strong class="{{ $isOverdue ? 'text-rose-600' : 'text-[#015987]' }}">
                                                {{ $taskItem->deadline_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                                            </strong>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route($rolePrefix . '.tasks.show', $taskItem) }}"
                                        class="px-3 py-1.5 rounded-xl border border-slate-200 hover:border-[#015987] text-xs font-semibold text-[#015987] transition"
                                    >
                                        Detail
                                    </a>

                                    @can('update', $taskItem)
                                        <a
                                            href="{{ route('pic.tasks.edit', $taskItem) }}"
                                            class="px-3 py-1.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-semibold text-white transition"
                                        >
                                            Edit / Deadline
                                        </a>
                                    @endcan
                                </div>
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                                {{ $taskItem->description }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3">
                    {{ $tasks->links() }}
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
