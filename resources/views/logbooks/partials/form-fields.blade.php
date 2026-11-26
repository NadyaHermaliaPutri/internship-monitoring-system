{{--
    Field form logbook ringkas (dipakai halaman create, edit, dan modal tambah di index).
    Variabel:
      - $prefix     : awalan id elemen agar unik di satu halaman (default '')
      - $logbook    : model Logbook saat edit (opsional)
      - $todayDate, $earliestDate
--}}
@php
    $prefix = $prefix ?? '';
    $logbook = $logbook ?? null;
    $inputClass = 'w-full px-3.5 py-2.5 text-sm sm:text-xs rounded-xl border text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-[#015987] transition';
    $okClass = 'border-slate-200 bg-slate-50/60';
    $errClass = 'border-rose-400 bg-rose-50/40';
    $selectedActivityStatus = old('activity_status', $logbook?->activity_status?->value ?? 'sedang_dikerjakan');
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}log_date" class="block text-xs font-bold text-slate-700 mb-1.5">
            Tanggal Kegiatan <span class="text-rose-500">*</span>
        </label>
        <input
            type="date"
            id="{{ $prefix }}log_date"
            name="log_date"
            value="{{ old('log_date', $logbook?->log_date?->toDateString() ?? $todayDate) }}"
            min="{{ $earliestDate }}"
            max="{{ $todayDate }}"
            required
            class="{{ $inputClass }} {{ $errors->has('log_date') ? $errClass : $okClass }}"
        >
        <p class="text-[11px] text-slate-400 mt-1">
            Rentang diizinkan: {{ \Carbon\Carbon::parse($earliestDate)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($todayDate)->translatedFormat('d M Y') }}
        </p>
        @error('log_date')
            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="{{ $prefix }}activity_status" class="block text-xs font-bold text-slate-700 mb-1.5">
            Status <span class="text-rose-500">*</span>
        </label>
        <select
            id="{{ $prefix }}activity_status"
            name="activity_status"
            required
            class="{{ $inputClass }} {{ $errors->has('activity_status') ? $errClass : $okClass }}"
        >
            <option value="sedang_dikerjakan" @selected($selectedActivityStatus === 'sedang_dikerjakan')>Sedang Dikerjakan</option>
            <option value="menunggu_review" @selected($selectedActivityStatus === 'menunggu_review')>Menunggu Review</option>
            <option value="selesai" @selected($selectedActivityStatus === 'selesai')>Selesai</option>
            <option value="baru_dikasih" @selected($selectedActivityStatus === 'baru_dikasih')>Baru Diberikan</option>
        </select>
        <p class="text-[11px] text-slate-400 mt-1">
            "Baru Diberikan" otomatis terpasang untuk tugas baru dari PIC yang belum dikerjakan.
        </p>
        @error('activity_status')
            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label for="{{ $prefix }}title" class="block text-xs font-bold text-slate-700 mb-1.5">
        Kegiatan <span class="text-rose-500">*</span>
    </label>
    <input
        type="text"
        id="{{ $prefix }}title"
        name="title"
        value="{{ old('title', $logbook?->title) }}"
        maxlength="150"
        required
        placeholder="Contoh: Implementasi validasi form presensi harian"
        class="{{ $inputClass }} {{ $errors->has('title') ? $errClass : $okClass }}"
    >
    @error('title')
        <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 space-y-4">
    <div>
        <p class="text-xs font-bold text-slate-800">
            Hasil / Output Kegiatan <span class="text-rose-500">*</span>
        </p>
        <p class="text-[11px] text-slate-500 mt-0.5">
            Wajib mengisi keterangan/link hasil <strong>atau</strong> mengunggah screenshot bukti gambar (boleh keduanya).
        </p>
    </div>

    <div>
        <label for="{{ $prefix }}result" class="block text-xs font-bold text-slate-700 mb-1.5">
            Keterangan / Link Hasil
        </label>
        <textarea
            id="{{ $prefix }}result"
            name="result"
            rows="3"
            maxlength="2000"
            placeholder="Contoh: PR #12 siap ditinjau / link dokumen / ringkasan output yang dicapai"
            class="{{ $inputClass }} bg-white {{ $errors->has('result') ? $errClass : 'border-slate-200' }}"
        >{{ old('result', $logbook?->result) }}</textarea>
        @error('result')
            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="{{ $prefix }}evidence_file" class="block text-xs font-bold text-slate-700 mb-1.5">
            Screenshot / Bukti Gambar <span class="text-slate-400 font-normal">(JPG, PNG, WEBP, maks 5 MB)</span>
        </label>
        <input
            type="file"
            id="{{ $prefix }}evidence_file"
            name="evidence_file"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            class="block w-full text-xs text-slate-700 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#015987] file:text-white hover:file:bg-[#014467] file:cursor-pointer rounded-xl border {{ $errors->has('evidence_file') ? $errClass : 'border-slate-200 bg-white' }} p-1.5 transition"
        >
        @error('evidence_file')
            <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
        @enderror

        @if ($logbook?->evidenceMedia)
            <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 p-2.5 rounded-xl bg-sky-50/70 border border-sky-200/70 text-[11px]">
                <div class="text-slate-700">
                    <span class="font-bold text-[#015987]">Bukti saat ini:</span>
                    {{ $logbook->evidenceMedia->original_name }} ({{ $logbook->evidenceMedia->formatted_size }})
                </div>
                <a
                    href="{{ route('media.private.show', $logbook->evidenceMedia) }}"
                    target="_blank"
                    class="font-bold text-[#015987] hover:underline"
                >
                    Lihat Screenshot &rarr;
                </a>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
                Biarkan kosong jika tidak ingin mengganti gambar. Jika mengunggah gambar baru, bukti lama tetap tersimpan di riwayat revisi.
            </p>
        @endif
    </div>
</div>
