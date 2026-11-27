@extends('layouts.app')

@section('title', 'Edit Tugas & Deadline')
@section('page-title', 'Edit Tugas & Perpanjangan Deadline')
@section('page-subtitle', 'Perubahan batas waktu (deadline) langsung menjadi acuan perhitungan indikator Overdue')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-[#015987]">Perbarui Tugas / Deadline</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Peserta: <strong>{{ $task->intern?->name }}</strong> • Status saat ini: <strong>{{ $task->status->label() }}</strong>
                </p>
            </div>
            <a href="{{ route('pic.tasks.show', $task) }}" class="text-xs font-bold text-[#015987] hover:underline">
                &larr; Kembali ke Detail
            </a>
        </div>

        <form method="POST" action="{{ route('pic.tasks.update', $task) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Judul Tugas <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $task->title) }}"
                    maxlength="180"
                    required
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('title') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                >
                @error('title')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="deadline_at" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Batas Waktu Pengumpulan Baru (Deadline WIB) <span class="text-rose-500">*</span>
                </label>
                <input
                    type="datetime-local"
                    id="deadline_at"
                    name="deadline_at"
                    value="{{ old('deadline_at', $task->deadline_at?->timezone('Asia/Jakarta')->format('Y-m-d\TH:i')) }}"
                    required
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('deadline_at') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                >
                <p class="text-[11px] text-slate-400 mt-1">
                    Perpanjangan deadline tidak akan menggandakan catatan logbook otomatis peserta.
                </p>
                @error('deadline_at')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Deskripsi &amp; Spesifikasi Tugas <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    placeholder="Uraikan spesifikasi pekerjaan dan hasil yang perlu dikumpulkan peserta (opsional)..."
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('description') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                >{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="attachment_file" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Lampiran Tugas / Brief <span class="text-slate-400 font-normal">(Opsional — Privat)</span>
                </label>
                <input
                    type="file"
                    id="attachment_file"
                    name="attachment_file"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.zip,.rar"
                    class="w-full px-3 py-2 text-xs rounded-xl border @error('attachment_file') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#015987] file:text-white hover:file:bg-[#014467] file:cursor-pointer transition"
                >
                <p class="text-[11px] text-slate-400 mt-1">
                    Format: PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, JPG/PNG/WEBP, ZIP/RAR • Maksimal <strong>2 MB</strong>. Biarkan kosong jika tidak ingin mengganti lampiran.
                </p>
                @error('attachment_file')
                    <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror

                @if ($task->attachmentMedia)
                    <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 p-3 rounded-xl bg-sky-50/70 border border-sky-200/70 text-[11px]">
                        <div class="text-slate-700 min-w-0">
                            <span class="font-bold text-[#015987]">Lampiran saat ini:</span>
                            <span class="break-all">{{ $task->attachmentMedia->original_name }}</span>
                            <span class="text-slate-500">({{ $task->attachmentMedia->formatted_size }})</span>
                        </div>
                        <a
                            href="{{ route('media.private.show', ['mediaFile' => $task->attachmentMedia->id, 'download' => 1]) }}"
                            class="font-bold text-[#015987] hover:underline shrink-0"
                        >
                            Unduh Lampiran &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <a href="{{ route('pic.tasks.show', $task) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white text-xs font-bold">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
