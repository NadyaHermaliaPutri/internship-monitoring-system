<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $metadata['report_title'] ?? 'Laporan WANTRACE' }}</title>
    <style>
        @page {
            margin: 28px 32px 35px 32px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #015987;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .company-title {
            font-size: 16px;
            font-weight: bold;
            color: #015987;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 10px;
            color: #64748b;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 5px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
        }
        .meta-label {
            font-size: 10px;
            color: #64748b;
            font-weight: bold;
            width: 18%;
        }
        .meta-val {
            font-size: 10px;
            color: #1e293b;
            width: 32%;
        }
        .summary-boxes {
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-card {
            background-color: #eef4f8;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: center;
        }
        .summary-card-title {
            font-size: 9px;
            color: #015987;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-card-val {
            font-size: 14px;
            font-weight: bold;
            color: #014467;
            margin-top: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #015987;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #014467;
        }
        .data-table td {
            font-size: 10px;
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-info { background-color: #e0f2fe; color: #075985; }
        .badge-neutral { background-color: #f1f5f9; color: #475569; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 9px;
            color: #94a3b8;
            text-align: right;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
    @yield('styles')
</head>
<body>
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 65%;">
                <div class="company-title">WANTRACE &bull; PT WAN TEKNOLOGI INTERNASIONAL</div>
                <div class="company-sub">Sistem Monitoring &amp; Evaluasi Kegiatan Magang</div>
                <div class="report-title">{{ $metadata['report_title'] ?? 'Laporan Evaluasi' }}</div>
            </td>
            <td style="width: 35%; text-align: right; vertical-align: bottom;">
                <div style="font-size: 9px; color: #64748b;">Dokumen Internal Perusahaan</div>
                <div style="font-size: 9px; color: #64748b;">Dicetak: {{ $metadata['generated_at']?->translatedFormat('d F Y, H:i') ?? now()->translatedFormat('d F Y, H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="meta-label">Periode Laporan:</td>
            <td class="meta-val">
                <strong>{{ \Illuminate\Support\Carbon::parse($metadata['start_date'])->translatedFormat('d M Y') }}</strong> s.d. 
                <strong>{{ \Illuminate\Support\Carbon::parse($metadata['end_date'])->translatedFormat('d M Y') }}</strong>
                ({{ $metadata['working_days_count'] ?? 0 }} Hari Kerja)
            </td>
            <td class="meta-label">Cakupan Peserta:</td>
            <td class="meta-val"><strong>{{ $metadata['intern_scope_label'] ?? 'Semua Peserta' }}</strong></td>
        </tr>
    </table>

    @yield('content')

    <div class="footer">
        Halaman rekap resmi WANTRACE &bull; Digenerate secara otomatis pada {{ $metadata['generated_at']?->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
