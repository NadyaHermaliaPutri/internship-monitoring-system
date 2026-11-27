<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $metadata['report_title'] ?? 'Laporan WANTRACE' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 24px 28px 36px 28px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* 1. KOP SURAT RESMI PERUSAHAAN (BERBASIS TIPOGRAFI TANPA LOGO) */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }
        .kop-instansi-cell {
            vertical-align: middle;
            text-align: left;
        }
        .kop-brand-badge {
            display: inline-block;
            font-size: 7.5px;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 2px;
        }
        .kop-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #015987;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .kop-sub {
            font-size: 8px;
            font-weight: 600;
            color: #475569;
            margin-top: 2px;
            letter-spacing: 0.2px;
        }
        .kop-meta-cell {
            width: 200px;
            vertical-align: middle;
            text-align: right;
        }
        .badge-doc-internal {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 3px 8px;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .kop-print-date {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 3px;
        }
        .kop-divider-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }
        .kop-divider-thick {
            height: 2px;
            background-color: #015987;
        }
        .kop-divider-thin {
            height: 1px;
            background-color: #94a3b8;
            margin-top: 2px;
        }

        /* 2. BLOK JUDUL DOKUMEN */
        .report-header-box {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-main-title {
            font-size: 12.5px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .report-subtitle {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }

        /* 3. BLOK METADATA LAPORAN */
        .meta-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #015987;
        }
        .meta-container td {
            padding: 5px 8px;
            vertical-align: middle;
            font-size: 8px;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 16%;
        }
        .meta-val {
            color: #0f172a;
            width: 34%;
        }

        /* 4. SUMMARY STRIP (KPI BOXES) */
        .summary-boxes {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            margin-bottom: 12px;
        }
        .summary-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-top: 2.5px solid #015987;
            padding: 5px 6px;
            text-align: center;
            border-radius: 2px;
        }
        .summary-card-title {
            font-size: 7px;
            color: #475569;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.2px;
        }
        .summary-card-val {
            font-size: 11.5px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        /* 5. TABEL DATA UTAMA */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            table-layout: fixed;
            word-wrap: break-word;
        }
        .data-table thead {
            display: table-header-group;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .data-table th {
            background-color: #015987;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #014467;
            vertical-align: middle;
            letter-spacing: 0.2px;
            word-wrap: break-word;
        }
        .data-table td {
            font-size: 7.5px;
            padding: 4.5px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            color: #1e293b;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* 6. BADGES STATUS */
        .badge {
            display: inline-block;
            padding: 1.5px 4px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
            border: 1px solid transparent;
            letter-spacing: 0.1px;
            white-space: nowrap;
        }
        .badge-success { background-color: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .badge-warning { background-color: #fef3c7; color: #92400e; border-color: #fde68a; }
        .badge-danger  { background-color: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .badge-info    { background-color: #e0f2fe; color: #075985; border-color: #bae6fd; }
        .badge-neutral { background-color: #f1f5f9; color: #475569; border-color: #e2e8f0; }

        /* 7. FOOTER RESMI DOKUMEN */
        .footer-table {
            position: fixed;
            bottom: -22px;
            left: 0;
            right: 0;
            width: 100%;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            font-size: 7px;
            color: #64748b;
        }
        .footer-left {
            text-align: left;
            width: 70%;
        }
        .footer-right {
            text-align: right;
            width: 30%;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
    @yield('styles')
</head>
<body>
    @php
        $printDate = $metadata['generated_at'] ?? now();
    @endphp

    <!-- 1. KOP SURAT RESMI PERUSAHAAN (TANPA LOGO GAMBAR) -->
    <table class="kop-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="kop-instansi-cell">
                <div class="kop-brand-badge">WANTRACE &bull; SISTEM MONITORING &amp; EVALUASI MAGANG</div>
                <div class="kop-title">PT WAN TEKNOLOGI INTERNASIONAL</div>
                <div class="kop-sub">Divisi Human Resources &bull; Pengawasan Teknis &amp; Rekapitulasi Historis Peserta</div>
            </td>
            <td class="kop-meta-cell">
                <span class="badge-doc-internal">DOKUMEN INTERNAL PERUSAHAAN</span>
                <div class="kop-print-date">
                    Dicetak: {{ $printDate->translatedFormat('d F Y, H:i') }} WIB
                </div>
            </td>
        </tr>
    </table>

    <table class="kop-divider-table" cellpadding="0" cellspacing="0">
        <tr><td class="kop-divider-thick"></td></tr>
        <tr><td class="kop-divider-thin"></td></tr>
    </table>

    <!-- 2. BLOK JUDUL DOKUMEN -->
    <div class="report-header-box">
        <h1 class="report-main-title">{{ $metadata['report_title'] ?? 'Laporan Evaluasi Magang' }}</h1>
        <div class="report-subtitle">Laporan resmi rekapitulasi data historis kegiatan peserta program magang industri</div>
    </div>

    <!-- 3. BLOK METADATA TERSTRUKTUR (2 BARIS RAPI) -->
    <table class="meta-container" cellpadding="0" cellspacing="0">
        <tr>
            <td class="meta-label">Periode Laporan:</td>
            <td class="meta-val">
                <strong>{{ \Illuminate\Support\Carbon::parse($metadata['start_date'])->translatedFormat('d F Y') }}</strong> s.d. 
                <strong>{{ \Illuminate\Support\Carbon::parse($metadata['end_date'])->translatedFormat('d F Y') }}</strong>
                ({{ $metadata['working_days_count'] ?? 0 }} Hari Kerja Efektif)
            </td>
            <td class="meta-label">Cakupan Peserta:</td>
            <td class="meta-val">
                <strong>{{ $metadata['intern_scope_label'] ?? 'Semua Peserta Magang' }}</strong>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Jenis Dokumen:</td>
            <td class="meta-val">
                {{ strtoupper($metadata['report_type'] ?? 'EVALUASI') }} &bull; Arsip Terverifikasi
            </td>
            <td class="meta-label">Waktu Ekspor:</td>
            <td class="meta-val">
                {{ $printDate->translatedFormat('l, d F Y - H:i') }} WIB
            </td>
        </tr>
    </table>

    <!-- 4. KONTEN UTAMA LAPORAN -->
    @yield('content')

    <!-- 5. FOOTER DOKUMEN RESMI (SEMUA HALAMAN) -->
    <table class="footer-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="footer-left">
                Dokumen resmi hasil olahan sistem WANTRACE &bull; PT WAN Teknologi Internasional &bull; Kerahasiaan Terjaga
            </td>
            <td class="footer-right">
                Halaman Terverifikasi
            </td>
        </tr>
    </table>
</body>
</html>
