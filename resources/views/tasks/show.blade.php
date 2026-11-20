@extends('layouts.app')

@section('title', 'Detail Tugas')
@section('page-title', 'Detail Tugas Magang')
@section('page-subtitle', 'Informasi penugasan, batas waktu (deadline), dan pengumpulan hasil kerja')

@section('content')
@php
    $user = auth()->user();
    $rolePrefix = $user->role;
    $isOverdue = $task->isOverdue();
    $statusBadge = match ($task->status->value) {
        'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'submitted', 'resubmitted' => 'bg-amber-50 text-amber-700 border-amber-200',
        'revision_requested' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
    $nextSubmissionNumber = ($task->submissions->max('submission_number') ?? 0) + 1;
@endphp

<div class="max-w-4xl mx-auto space-y-6">

    @if (session('status'))
        <div class="flex items-center gap-2.5 px-4 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Kartu Informasi Utama Tugas --}}
    <div class="bg-white rounded-2xl border {{ $isOverdue ? 'border-rose-300' : 'border-slate-200/70' }} shadow-xs p-6 sm:p-8 space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4 pb-5 border-b border-slate-100">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                        {{ $task->status->label() }}
                    </span>

                    @if ($isOverdue)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-300">
                            OVERDUE (Lewat Deadline)
                        </span>
                    @endif

                    @if ($task->submissions->isNotEmpty())
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50 text-[#015987] border border-sky-200">
                            {{ $task->submissions->count() }}x Pengumpulan
                        </span>
                    @endif
                </div>

                <h2 class="text-lg font-extrabold text-slate-900">{{ $task->title }}</h2>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                    <span>Peserta: <strong class="text-slate-700">{{ $task->intern?->name }}</strong></span>
                    <span>PIC Pembimbing: <strong class="text-slate-700">{{ $task->pic?->name }}</strong></span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a
                    href="{{ route($rolePrefix . '.tasks.index') }}"
                    class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                >
                    &larr; Daftar Tugas
                </a>

                @can('start', $task)
                    <form method="POST" action="{{ route('intern.tasks.start', $task) }}">
                        @csrf
                        @method('PATCH')
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl border border-[#015987] text-[#015987] hover:bg-sky-50 text-xs font-bold transition"
                        >
                            Mulai Kerjakan Tugas
                        </button>
                    </form>
                @endcan

                @can('update', $task)
                    <a
                        href="{{ route('pic.tasks.edit', $task) }}"
                        class="px-4 py-2 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-bold text-white"
                    >
                        Edit Tugas / Deadline
                    </a>
                @endcan
            </div>
        </div>

        {{-- Informasi Waktu --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Waktu Ditugaskan</p>
                <p class="text-xs font-bold text-slate-800 mt-1">
                    {{ $task->assigned_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                </p>
            </div>

            <div class="p-3.5 rounded-xl {{ $isOverdue ? 'bg-rose-50/70 border-rose-200' : 'bg-slate-50/70 border-slate-100' }} border">
                <p class="text-[10px] font-bold {{ $isOverdue ? 'text-rose-500' : 'text-slate-400' }} uppercase">Batas Waktu (Deadline)</p>
                <p class="text-xs font-bold {{ $isOverdue ? 'text-rose-700' : 'text-[#015987]' }} mt-1">
                    {{ $task->deadline_at ? $task->deadline_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') . ' WIB' : '-' }}
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Waktu Diterima (Accepted)</p>
                <p class="text-xs font-bold text-slate-800 mt-1">
                    {{ $task->accepted_at ? $task->accepted_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') . ' WIB' : 'Belum Selesai' }}
                </p>
            </div>
        </div>

        {{-- Deskripsi Tugas --}}
        <div>
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Deskripsi &amp; Instruksi Tugas</h3>
            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-100 text-xs text-slate-800 whitespace-pre-line leading-relaxed">{{ $task->description }}</div>
        </div>

        {{-- Keterkaitan Logbook Otomatis --}}
        @if ($task->assignmentLogbook)
            <div class="p-4 rounded-xl bg-sky-50/60 border border-sky-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <p class="text-xs font-bold text-[#015987]">Tercatat Otomatis pada Logbook Harian Peserta</p>
                    <p class="text-[11px] text-slate-600">
                        Tanggal Logbook: {{ $task->assignmentLogbook->log_date->translatedFormat('d M Y') }} • {{ $task->assignmentLogbook->title }}
                    </p>
                </div>
                <a
                    href="{{ route($rolePrefix . '.logbooks.show', $task->assignmentLogbook) }}"
                    class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white border border-sky-200 text-xs font-bold text-[#015987] hover:bg-sky-50 transition"
                >
                    Lihat Catatan Logbook &rarr;
                </a>
            </div>
        @endif

    </div>

    {{-- FORM PENGUMPULAN / PENGUMPULAN ULANG TUGAS (KHUSUS PESERTA SAAT DIIZINKAN POLICY) --}}
    @can('submit', $task)
        <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-[#015987]">
                        {{ $task->status->value === 'revision_requested' ? 'Kumpulkan Ulang Hasil Revisi (Versi Ke-' . $nextSubmissionNumber . ')' : 'Formulir Pengumpulan Hasil Tugas (Versi Ke-' . $nextSubmissionNumber . ')' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Isi minimal salah satu bukti pengerjaan: <strong>Tautan URL</strong> dan/atau <strong>Unggah Berkas/Video</strong>. Setiap pengumpulan disimpan sebagai riwayat baru dan tidak menimpa kiriman sebelumnya.
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-[#015987] border border-sky-200">
                    Penyimpanan Privat Terotorisasi
                </span>
            </div>

            <form method="POST" action="{{ route('intern.tasks.submissions.store', $task) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Input Bukti URL --}}
                    <div>
                        <label for="submission_url" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Tautan / URL Bukti Tugas <span class="text-slate-400 font-normal">(GitHub / Figma / Drive / Demo)</span>
                        </label>
                        <input
                            type="url"
                            id="submission_url"
                            name="submission_url"
                            value="{{ old('submission_url') }}"
                            placeholder="https://github.com/... atau https://figma.com/..."
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('submission_url') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">
                            Wajib diawali <code>http://</code> atau <code>https://</code> jika diisi.
                        </p>
                        @error('submission_url')
                            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Input Bukti File / Video --}}
                    <div>
                        <label for="evidence_file" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Unggah Berkas / Video Bukti <span class="text-slate-400 font-normal">(Privat)</span>
                        </label>
                        <input
                            type="file"
                            id="evidence_file"
                            name="evidence_file"
                            accept=".mp4,.webm,.mov,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.zip,.rar"
                            class="w-full px-3 py-2 text-xs rounded-xl border @error('evidence_file') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#015987] file:text-white hover:file:bg-[#014467] transition"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">
                            Video (MP4/WEBM/MOV) maks. <strong>15 MB</strong> • Dokumen/Gambar/ZIP maks. <strong>10 MB</strong>.
                        </p>
                        @error('evidence_file')
                            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="submission_note" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Catatan Pengumpulan <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea
                        id="submission_note"
                        name="submission_note"
                        rows="3"
                        placeholder="Tuliskan ringkasan hasil pengerjaan, perubahan revisi, atau petunjuk pengujian untuk PIC..."
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('submission_note') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                    >{{ old('submission_note') }}</textarea>
                    @error('submission_note')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <button
                        type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs shadow-xs transition"
                    >
                        {{ $task->status->value === 'revision_requested' ? 'Kirim Pengumpulan Ulang (Ke-' . $nextSubmissionNumber . ')' : 'Kirim Hasil Tugas (Ke-' . $nextSubmissionNumber . ')' }}
                    </button>
                </div>
            </form>
        </div>
    @elseif ($user->isIntern())
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 flex items-center justify-between gap-3">
            <div>
                <p class="font-bold text-slate-800">Status Pengumpulan: <span class="text-[#015987]">{{ $task->status->label() }}</span></p>
                <p class="mt-0.5">
                    @if ($task->status->value === 'accepted')
                        Tugas ini telah diterima (Accepted) oleh PIC Pembimbing dan telah dikunci.
                    @else
                        Pengumpulan Anda sedang menunggu peninjauan dari PIC Pembimbing. Anda dapat mengirim pengumpulan ulang apabila PIC meminta revisi.
                    @endif
                </p>
            </div>
        </div>
    @endif

    {{-- DAFTAR RIWAYAT PENGUMPULAN (SUBMISSIONS) --}}
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-[#015987]">Riwayat Pengumpulan Hasil Tugas ({{ $task->submissions->count() }})</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Setiap pengiriman tercatat berurutan (Ke-1, Ke-2, dst.) sesuai waktu server Asia/Jakarta.
                </p>
            </div>
        </div>

        @if ($task->submissions->isEmpty())
            <div class="text-center py-8 px-4 rounded-xl border border-dashed border-slate-200 bg-slate-50/40">
                <p class="text-xs font-bold text-slate-600">Belum ada hasil tugas yang dikumpulkan</p>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Riwayat pengiriman bukti URL maupun berkas/video akan muncul di sini.
                </p>
            </div>
        @else
            <div class="space-y-3.5">
                @foreach ($task->submissions as $sub)
                    <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/40 space-y-3 text-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#015987] text-white">
                                    Pengumpulan Ke-{{ $sub->submission_number }}
                                </span>
                                @if ($loop->first)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Versi Terbaru
                                    </span>
                                @endif
                            </div>

                            <span class="text-[11px] text-slate-500 font-medium">
                                Dikirim pada {{ $sub->submitted_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB
                                oleh <strong class="text-slate-700">{{ $sub->submitter?->name ?? 'Peserta' }}</strong>
                            </span>
                        </div>

                        @if ($sub->submission_note)
                            <div class="p-3 rounded-xl bg-white border border-slate-200/70 text-slate-700 whitespace-pre-line leading-relaxed">{{ $sub->submission_note }}</div>
                        @endif

                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            @if ($sub->submission_url)
                                <a
                                    href="{{ $sub->submission_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-[#015987] text-[#015987] font-bold text-xs transition"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    <span>Buka Tautan URL Bukti</span>
                                </a>
                            @endif

                            @if ($sub->evidenceMedia)
                                <div class="inline-flex flex-wrap items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs">
                                    <span class="font-semibold text-slate-700 truncate max-w-xs">
                                        {{ $sub->evidenceMedia->original_name }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        ({{ $sub->evidenceMedia->formatted_size }})
                                    </span>
                                    <a
                                        href="{{ route('media.private.show', $sub->evidenceMedia) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-[#015987] font-bold hover:underline ml-1"
                                    >
                                        Lihat
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a
                                        href="{{ route('media.private.show', ['mediaFile' => $sub->evidenceMedia->id, 'download' => 1]) }}"
                                        class="text-emerald-700 font-bold hover:underline"
                                    >
                                        Unduh
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
