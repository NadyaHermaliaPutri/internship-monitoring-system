<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aturan Waktu & Jam Kerja PT WAN Teknologi Internasional
    |--------------------------------------------------------------------------
    | Seluruh penentuan waktu wajib merujuk ke timezone Asia/Jakarta (WIB).
    | Jam kerja operasional resmi: 08.00 - 17.00 WIB.
    | Toleransi keterlambatan check-in sampai 08.05 WIB inklusif (on_time).
    | Setelah 08.05 WIB dianggap terlambat (late).
    */
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    'work_hours' => [
        'start' => '08:00',
        'tolerance_cutoff' => '08:05', // Inklusif sampai 08:05:59 WIB dianggap tepat waktu
        'end' => '17:00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Unggah Berkas & Dokumen Bukti (Private Local Storage)
    |--------------------------------------------------------------------------
    | Batas ukuran file dan MIME yang diizinkan sesuai keputusan modul.
    */
    'uploads' => [
        'disk' => 'local_private',

        // Foto selfie bukti presensi masuk harian
        'attendance_photo' => [
            'max_size_kb' => 2048, // 2 MB
            'mimes' => ['jpeg', 'jpg', 'png', 'webp'],
            'directory' => 'presensi',
        ],

        // Bukti pengajuan izin / sakit (surat dokter / dokumen)
        'leave_evidence' => [
            'max_size_kb' => 5120, // 5 MB
            'mimes' => ['jpeg', 'jpg', 'png', 'pdf'],
            'directory' => 'izin',
        ],

        // Bukti pengumpulan tugas peserta magang (video maks 15 MB, dokumen/gambar/arsip maks 10 MB)
        'task_evidence' => [
            'max_video_size_kb' => 15360, // 15 MB
            'max_document_size_kb' => 10240, // 10 MB
            'video_mimes' => ['mp4', 'webm', 'mov'],
            'document_mimes' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp', 'zip', 'rar'],
            'mimes' => ['mp4', 'webm', 'mov', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp', 'zip', 'rar'],
            'directory' => 'tugas',
        ],

        // Screenshot / gambar bukti hasil kegiatan logbook harian (maks 5 MB)
        'logbook_evidence' => [
            'max_size_kb' => 5120, // 5 MB
            'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
            'directory' => 'logbook',
        ],

        // File lampiran / brief / referensi tugas dari PIC (opsional, maks 2 MB)
        'task_attachment' => [
            'max_size_kb' => 2048, // 2 MB
            'mimes' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp', 'zip', 'rar'],
            'directory' => 'tugas-lampiran',
        ],
    ],
];
