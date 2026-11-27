@extends('layouts.app')

@section('title', 'Detail Logbook')
@section('page-title', 'Detail Logbook Harian')
@section('page-subtitle', 'Informasi lengkap aktivitas harian, verifikasi PIC, dan jejak revisi')

@section('content')
@php
    $user = auth()->user();
    $rolePrefix = $user->role;
@endphp

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Banner Status Final Approved --}}
    @if ($logbook->isManual() && $logbook->status?->value === 'approved')
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-0.5 text-xs">
                <p class="font-extrabold text-emerald-800">Logbook Telah Disetujui (Approved) &amp; Terkunci</p>
                <p class="text-emerald-700">
                    Diverifikasi oleh <strong>{{ $logbook->reviewer?->name ?? 'PIC Pembimbing' }}</strong>
                    @if ($logbook->reviewed_at)
                        pada <strong>{{ $logbook->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}</strong>
                    @endif
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white uppercase tracking-wider">
                Terverifikasi
            </span>
        </div>
    @endif

    {{-- Banner Permintaan Revisi dari PIC (Menonjol untuk Peserta & PIC) --}}
    @if ($logbook->isManual() && $logbook->status?->value === 'revision_requested')
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 space-y-2 text-xs">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-extrabold text-rose-800">
                    Permintaan Revisi dari PIC ({{ $logbook->reviewer?->name ?? 'PIC Pembimbing' }})
                </p>
                @if ($logbook->reviewed_at)
                    <span class="text-[11px] text-rose-600">
                        {{ $logbook->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}
                    </span>
                @endif
            </div>
            <p class="text-rose-800 whitespace-pre-line leading-relaxed">{{ $logbook->review_note }}</p>
            @can('update', $logbook)
                <div class="pt-1">
                    <a
                        href="{{ route('intern.logbooks.edit', $logbook) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition"
                    >
                        Perbaiki &amp; Kirim Ulang Logbook &rarr;
                    </a>
                </div>
            @endcan
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4 pb-5 border-b border-slate-100">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-[#015987]">
                        {{ $logbook->log_date->translatedFormat('l, d F Y') }}
                    </span>

                    @if ($logbook->isTaskAssignment())
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                            Otomatis Penugasan PIC
                        </span>
                    @elseif ($logbook->relatedTask)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Terkait Tugas PIC ({{ $logbook->relatedTask->pic?->name ?? 'PIC' }})
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                            Kegiatan Mandiri / Umum
                        </span>
                    @endif

                    @if ($logbook->isManual() && $logbook->status)
                        @php
                            $statusClasses = match ($logbook->status->value) {
                                'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'revision_requested' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-amber-50 text-amber-700 border-amber-200',
                            };
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClasses }}">
                            {{ $logbook->status->label() }}
                        </span>
                        @if ($logbook->status->value !== 'approved' && $logbook->activity_status)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $logbook->activity_status->badgeClasses() }}">
                                Progres: {{ $logbook->activity_status->label() }}
                            </span>
                        @endif
                    @elseif ($logbook->isTaskAssignment() && $logbook->task?->status)
                        @php
                            $taskStatusClasses = match ($logbook->task->status->value) {
                                'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'revision_requested' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'submitted', 'resubmitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $taskStatusClasses }}">
                            Status Tugas: {{ $logbook->task->status->label() }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                            Tercatat Otomatis
                        </span>
                    @endif
                </div>

                <h2 class="text-lg font-extrabold text-slate-900">{{ $logbook->title }}</h2>
                <p class="text-xs text-slate-500">
                    Peserta: <strong class="text-slate-700">{{ $logbook->intern?->name }}</strong> ({{ $logbook->intern?->email }})
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route($rolePrefix . '.logbooks.index') }}"
                    class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                >
                    &larr; Riwayat
                </a>

                @can('complete', $logbook)
                    <form method="POST" action="{{ route('intern.logbooks.complete', $logbook) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white transition cursor-pointer"
                        >
                            Tandai Selesai
                        </button>
                    </form>
                @endcan

                @can('update', $logbook)
                    <a
                        href="{{ route('intern.logbooks.edit', $logbook) }}"
                        class="px-4 py-2 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-bold text-white"
                    >
                        Edit Logbook
                    </a>
                @endcan
            </div>
        </div>

        <div class="space-y-4">
            @if (filled($logbook->activity_description) && trim($logbook->activity_description) !== trim($logbook->title))
                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Uraian Aktivitas</h3>
                    <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-100 text-xs text-slate-800 whitespace-pre-line leading-relaxed">{{ $logbook->activity_description }}</div>
                </div>
            @endif

            <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hasil / Output Kegiatan</h3>

                @if (filled($logbook->result))
                    <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-100 text-xs text-slate-700 whitespace-pre-line leading-relaxed">{{ $logbook->result }}</div>
                @elseif (! $logbook->evidenceMedia)
                    <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-100 text-xs text-slate-500">Tidak ada catatan hasil tambahan.</div>
                @endif

                @if ($logbook->evidenceMedia)
                    <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-xs">
                                <p class="font-bold text-slate-800">Screenshot / Bukti Kegiatan</p>
                                <p class="text-[11px] text-slate-500">
                                    {{ $logbook->evidenceMedia->original_name }} ({{ $logbook->evidenceMedia->formatted_size }})
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a
                                    href="{{ route('media.private.show', $logbook->evidenceMedia) }}"
                                    target="_blank"
                                    class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 hover:border-[#015987] text-xs font-semibold text-[#015987] transition"
                                >
                                    Lihat Penuh
                                </a>
                                <a
                                    href="{{ route('media.private.show', ['mediaFile' => $logbook->evidenceMedia, 'download' => 1]) }}"
                                    class="px-3 py-1.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-xs font-semibold text-white transition"
                                >
                                    Unduh
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('media.private.show', $logbook->evidenceMedia) }}" target="_blank" class="block">
                            <img
                                src="{{ route('media.private.show', $logbook->evidenceMedia) }}"
                                alt="Screenshot bukti logbook {{ $logbook->title }}"
                                class="max-h-72 rounded-xl border border-slate-200 object-contain bg-white"
                            >
                        </a>
                    </div>
                @endif
            </div>

            @if ($logbook->relatedTask)
                <div class="p-4 rounded-xl bg-indigo-50/60 border border-indigo-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-indigo-900">
                            Terkait Tugas: {{ $logbook->relatedTask->title }}
                        </p>
                        <p class="text-[11px] text-indigo-700 mt-0.5">
                            PIC Pemberi Tugas: <strong>{{ $logbook->relatedTask->pic?->name ?? 'PIC' }}</strong>
                        </p>
                    </div>
                    <a
                        href="{{ route($rolePrefix . '.tasks.show', $logbook->relatedTask->id) }}"
                        class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white border border-indigo-200 text-xs font-bold text-indigo-800 hover:bg-indigo-50 transition"
                    >
                        Lihat Detail Tugas &rarr;
                    </a>
                </div>
            @endif

            @if ($logbook->task_id)
                <div class="p-4 rounded-xl bg-sky-50/60 border border-sky-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-[#015987]">Terhubung dengan Penugasan PIC</p>
                        <p class="text-[11px] text-slate-600 mt-0.5">
                            Catatan ini dibuat otomatis oleh sistem saat PIC memberikan tugas (bukan penanda tugas selesai dan tidak memerlukan approval logbook).
                        </p>
                    </div>
                    <a
                        href="{{ route($rolePrefix . '.tasks.show', $logbook->task_id) }}"
                        class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white border border-sky-200 text-xs font-bold text-[#015987] hover:bg-sky-50 transition"
                    >
                        Lihat Detail Tugas &rarr;
                    </a>
                </div>
            @endif

            @if ($logbook->reviewed_by || $logbook->review_note)
                <div class="p-4 rounded-xl bg-sky-50/60 border border-sky-200/70 space-y-1 text-xs">
                    <p class="font-bold text-[#015987]">
                        Catatan Tinjauan PIC {{ $logbook->reviewer ? '(' . $logbook->reviewer->name . ')' : '' }}
                        @if ($logbook->reviewed_at)
                            <span class="font-normal text-slate-500">• {{ $logbook->reviewed_at->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}</span>
                        @endif
                    </p>
                    <p class="text-slate-700 whitespace-pre-line">{{ $logbook->review_note ?: 'Disetujui tanpa catatan tambahan.' }}</p>
                </div>
            @endif
        </div>

    </div>

    {{-- PANEL REVIEW & APPROVAL LOGBOOK OLEH PIC (HANYA UNTUK LOGBOOK MANUAL BERSTATUS PENDING) --}}
    @can('review', $logbook)
        <div class="bg-white rounded-2xl border-2 border-amber-300 shadow-xs p-6 sm:p-8 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-[#015987]">Panel Verifikasi &amp; Review Logbook PIC</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Berikan keputusan terhadap catatan aktivitas harian peserta: <strong>Setujui Logbook (Approved)</strong> atau <strong>Minta Revisi</strong>.
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    Menunggu Keputusan PIC
                </span>
            </div>

            <form method="POST" action="{{ route('pic.logbooks.review', $logbook) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        Keputusan Verifikasi PIC <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/40 cursor-pointer hover:bg-emerald-50 transition">
                            <input
                                type="radio"
                                name="decision"
                                value="approved"
                                @checked(old('decision', 'approved') === 'approved')
                                class="mt-0.5 accent-emerald-600"
                            >
                            <div class="text-xs">
                                <span class="font-bold text-emerald-800 block">Setujui Logbook (Approved)</span>
                                <span class="text-[11px] text-emerald-700">Logbook diverifikasi dan dikunci (peserta tidak dapat mengubah lagi).</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-rose-200 bg-rose-50/40 cursor-pointer hover:bg-rose-50 transition">
                            <input
                                type="radio"
                                name="decision"
                                value="revision_requested"
                                @checked(old('decision') === 'revision_requested')
                                class="mt-0.5 accent-rose-600"
                            >
                            <div class="text-xs">
                                <span class="font-bold text-rose-800 block">Minta Revisi (Revision Requested)</span>
                                <span class="text-[11px] text-rose-700">Wajib sertakan catatan revisi agar peserta dapat memperbaiki logbook.</span>
                            </div>
                        </label>
                    </div>
                    @error('decision')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="review_note" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Catatan / Feedback PIC <span class="text-slate-400 font-normal">(Wajib diisi jika Minta Revisi, minimal 5 karakter)</span>
                    </label>
                    <textarea
                        id="review_note"
                        name="review_note"
                        rows="3"
                        placeholder="Tuliskan catatan verifikasi atau bagian spesifik dari uraian/hasil yang perlu dilengkapi peserta..."
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('review_note') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition"
                    >{{ old('review_note') }}</textarea>
                    @error('review_note')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <button
                        type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white font-bold text-xs shadow-xs transition"
                    >
                        Simpan Keputusan Review Logbook
                    </button>
                </div>
            </form>
        </div>
    @endcan

    {{-- Riwayat Revisi --}}
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-4">
        <div class="pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-[#015987]">Riwayat Perubahan / Revisi ({{ $logbook->revisions->count() }})</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">
                Setiap perubahan pada logbook disimpan sebagai snapshot dan tidak menimpa catatan sebelumnya.
            </p>
        </div>

        @if ($logbook->revisions->isEmpty())
            <p class="text-xs text-slate-500 py-4">Belum ada riwayat perubahan pada logbook ini.</p>
        @else
            <div class="space-y-3">
                @foreach ($logbook->revisions as $revision)
                    <div class="p-4 rounded-xl bg-slate-50/60 border border-slate-200/70 space-y-2 text-xs">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-bold text-[#015987]">
                                Revisi #{{ $revision->revision_no }} — Versi Sebelum Perubahan
                            </span>
                            <span class="text-[11px] text-slate-500">
                                Diubah pada {{ $revision->created_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }}
                                oleh {{ $revision->editor?->name ?? 'Peserta' }}
                            </span>
                        </div>

                        <p class="text-slate-800 font-semibold">
                            [{{ $revision->log_date->translatedFormat('d M Y') }}] {{ $revision->title }}
                        </p>
                        @if (filled($revision->activity_description) && trim($revision->activity_description) !== trim($revision->title))
                            <p class="text-slate-600 whitespace-pre-line">{{ $revision->activity_description }}</p>
                        @endif

                        @if ($revision->result)
                            <p class="text-[11px] text-slate-500"><strong>Hasil sebelumnya:</strong> {{ $revision->result }}</p>
                        @endif

                        @if ($revision->evidenceMedia)
                            <p class="text-[11px]">
                                <a
                                    href="{{ route('media.private.show', $revision->evidenceMedia) }}"
                                    target="_blank"
                                    class="font-semibold text-[#015987] hover:underline"
                                >
                                    Lihat Screenshot Bukti Sebelumnya ({{ $revision->evidenceMedia->original_name }}) &rarr;
                                </a>
                            </p>
                        @endif

                        @if ($revision->review_note_before)
                            <p class="text-[11px] text-rose-600">
                                <strong>Catatan PIC saat itu ({{ $revision->status_before }}):</strong> {{ $revision->review_note_before }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
