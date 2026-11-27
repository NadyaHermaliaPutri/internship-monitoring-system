<table>
    <thead>
        <tr>
            <th colspan="6" style="font-size: 14pt; font-weight: bold; color: #015987;">WANTRACE - PT WAN TEKNOLOGI INTERNASIONAL</th>
        </tr>
        <tr>
            <th colspan="6" style="font-size: 12pt; font-weight: bold;">{{ $metadata['report_title'] ?? 'Rekapitulasi Logbook & Tugas' }}</th>
        </tr>
        <tr>
            <th colspan="6">Periode: {{ \Illuminate\Support\Carbon::parse($metadata['start_date'])->translatedFormat('d F Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($metadata['end_date'])->translatedFormat('d F Y') }} ({{ $metadata['working_days_count'] ?? 0 }} Hari Kerja Efektif)</th>
        </tr>
        <tr>
            <th colspan="6">Cakupan Peserta: {{ $metadata['intern_scope_label'] ?? 'Semua Peserta' }}</th>
        </tr>
        <tr>
            <th colspan="6">Waktu Cetak: {{ $metadata['generated_at']?->translatedFormat('d F Y, H:i') ?? now()->translatedFormat('d F Y, H:i') }} WIB</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; background-color: #eef4f8;">RINGKASAN LOGBOOK &amp; TUGAS</th>
            <th colspan="4"></th>
        </tr>
        <tr>
            <td colspan="2">Total Aktivitas &amp; Tugas:</td>
            <td style="font-weight: bold;">{{ $summary['total_items'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Logbook Manual:</td>
            <td style="font-weight: bold;">{{ $summary['manual_logbooks_count'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Tugas dari PIC:</td>
            <td style="font-weight: bold;">{{ $summary['tasks_count'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Tugas Selesai (Accepted):</td>
            <td style="font-weight: bold; color: green;">{{ $summary['tasks_accepted_count'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
        <tr style="background-color: #015987; color: #ffffff;">
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">No</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Tanggal</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Nama Peserta Magang</th>
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">Jenis</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Aktivitas / Kegiatan</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Penanggung Jawab</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['entries'] as $index => $item)
            <tr>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item['date'] ? $item['date']->format('Y-m-d') : '-' }}</td>
                <td style="border: 1px solid #cccccc; font-weight: bold;">{{ $item['intern_name'] }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $item['type_label'] }}</td>
                <td style="border: 1px solid #cccccc;">
                    <strong>{{ $item['title'] }}</strong>
                    @if($item['description'])
                        <div>{{ $item['description'] }}</div>
                    @endif
                    @if($item['meta_info'])
                        <div style="color: #666666;">{{ $item['meta_info'] }}</div>
                    @endif
                </td>
                <td style="border: 1px solid #cccccc; font-weight: bold;">{{ $item['pic_in_charge'] }} (Status: {{ $item['status_label'] }})</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; border: 1px solid #cccccc;">Tidak ada data logbook atau tugas pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
