<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $metadata['report_title'] ?? 'Laporan WANTRACE' }}</title>
    <style>
        @page {
            margin: 25px 30px 42px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* 1. KOP SURAT RESMI PERUSAHAAN */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }
        .kop-logo-cell {
            width: 58px;
            vertical-align: middle;
            padding-right: 12px;
        }
        .kop-logo {
            width: 50px;
            height: auto;
        }
        .kop-instansi-cell {
            vertical-align: middle;
            text-align: left;
        }
        .kop-title {
            font-size: 14px;
            font-weight: bold;
            color: #015987;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .kop-sub {
            font-size: 9px;
            font-weight: bold;
            color: #475569;
            margin-top: 3px;
            letter-spacing: 0.3px;
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
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 3px 8px;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .kop-print-date {
            font-size: 8px;
            color: #64748b;
            margin-top: 4px;
        }
        .kop-divider-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 14px;
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
            margin-bottom: 14px;
        }
        .report-main-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin: 0;
        }
        .report-subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        /* 3. BLOK METADATA LAPORAN (2 KOLOM RAPI) */
        .meta-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #015987;
        }
        .meta-container td {
            padding: 6px 10px;
            vertical-align: middle;
            font-size: 9px;
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
            border-spacing: 6px 0;
            margin-bottom: 14px;
        }
        .summary-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-top: 3px solid #015987;
            padding: 6px 8px;
            text-align: center;
            border-radius: 2px;
        }
        .summary-card-title {
            font-size: 8px;
            color: #475569;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.3px;
        }
        .summary-card-val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        /* 5. TABEL DATA UTAMA */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
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
            font-size: 9px;
            font-weight: bold;
            text-align: left;
            padding: 7px 8px;
            border: 1px solid #014467;
            vertical-align: middle;
            letter-spacing: 0.3px;
        }
        .data-table td {
            font-size: 9px;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            color: #1e293b;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* 6. BADGES STATUS */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
            border: 1px solid transparent;
            letter-spacing: 0.2px;
        }
        .badge-success { background-color: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .badge-warning { background-color: #fef3c7; color: #92400e; border-color: #fde68a; }
        .badge-danger  { background-color: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .badge-info    { background-color: #e0f2fe; color: #075985; border-color: #bae6fd; }
        .badge-neutral { background-color: #f1f5f9; color: #475569; border-color: #e2e8f0; }

        /* 7. FOOTER RESMI DOKUMEN */
        .footer-table {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            width: 100%;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            font-size: 8px;
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
        $logoPath = public_path('images/wi-logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
        $printDate = $metadata['generated_at'] ?? now();
    @endphp

    <!-- 1. KOP SURAT RESMI PERUSAHAAN -->
    <table class="kop-table" cellpadding="0" cellspacing="0">
        <tr>
            @if($logoBase64)
                <td class="kop-logo-cell">
                    <img src="{{ $logoBase64 }}" alt="Logo WAN" class="kop-logo">
                </td>
            @endif
            <td class="kop-instansi-cell">
                <div class="kop-title">PT WAN TEKNOLOGI INTERNASIONAL</div>
                <div class="kop-sub">WANTRACE &bull; Sistem Monitoring &amp; Evaluasi Kegiatan Magang</div>
            </td>
            <td class="kop-meta-cell">
                <span class="badge-doc-internal">Dokumen Internal Perusahaan</span>
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
