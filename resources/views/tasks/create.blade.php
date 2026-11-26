@extends('layouts.app')

@section('title', 'Buat Tugas Baru')
@section('page-title', 'Buat Tugas Peserta Magang')
@section('page-subtitle', 'Tugas baru otomatis tercatat pada logbook harian peserta')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-xs p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-[#015987]">Formulir Penugasan Baru</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pilih peserta bimbingan aktif dan tentukan batas waktu pengumpulan (WIB).
                </p>
            </div>
            <a href="{{ route('pic.tasks.index') }}" class="text-xs font-bold text-[#015987] hover:underline">
                &larr; Kembali ke Daftar Tugas
            </a>
        </div>

        @if ($internOptions->isEmpty())
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                Saat ini belum ada peserta magang aktif yang berada dalam periode penugasan Anda.
            </div>
        @else
            <form method="POST" action="{{ route('pic.tasks.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label for="intern_user_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Peserta Magang Penerima <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="intern_user_id"
                        name="intern_user_id"
                        required
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('intern_user_id') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                    >
                        <option value="">-- Pilih Peserta Magang Aktif --</option>
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
                        required
                        placeholder="Ringkasan nama tugas"
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('title') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                    >
                    @error('title')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="deadline_at" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Batas Waktu Pengumpulan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        id="deadline_at"
                        name="deadline_at"
                        value="{{ old('deadline_at', $defaultDeadline) }}"
                        required
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('deadline_at') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                    >
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
                        rows="5"
                        required
                        placeholder="Uraikan spesifikasi pekerjaan dan hasil yang perlu dikumpulkan peserta..."
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('description') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-800"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="attachment_file" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Lampiran Tugas / Brief <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input
                        type="file"
                        id="attachment_file"
                        name="attachment_file"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.zip,.rar"
                        class="w-full px-3 py-2 text-xs rounded-xl border @error('attachment_file') border-rose-400 bg-rose-50/40 @else border-slate-200 bg-slate-50/60 @enderror text-slate-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#015987] file:text-white hover:file:bg-[#014467] file:cursor-pointer transition"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">
                        Format: PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, JPG/PNG/WEBP, ZIP/RAR • Maksimal <strong>10 MB</strong>.
                    </p>
                    @error('attachment_file')
                        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-3 flex items-center justify-end gap-3">
                    <a href="{{ route('pic.tasks.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#015987] hover:bg-[#014467] text-white text-xs font-bold">
                        Simpan & Tugaskan
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
