<table>
    <thead>
        <tr>
            <th colspan="6" style="font-size: 14pt; font-weight: bold; color: #015987;">WANTRACE - PT WAN TEKNOLOGI INTERNASIONAL</th>
        </tr>
        <tr>
            <th colspan="6" style="font-size: 12pt; font-weight: bold;">{{ $metadata['report_title'] ?? 'Rekapitulasi Presensi' }}</th>
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
            <th colspan="2" style="font-weight: bold; background-color: #eef4f8;">RINGKASAN PRESENSI</th>
            <th colspan="4"></th>
        </tr>
        <tr>
            <td colspan="2">Total Hari Kerja Efektif:</td>
            <td style="font-weight: bold;">{{ $summary['working_days_count'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Total Hadir:</td>
            <td style="font-weight: bold; color: green;">{{ $summary['total_valid_presence'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Tepat Waktu:</td>
            <td style="font-weight: bold;">{{ $summary['on_time_count'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Terlambat:</td>
            <td style="font-weight: bold;">{{ $summary['late_count'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Tidak Hadir:</td>
            <td style="font-weight: bold; color: red;">{{ $summary['absent_count'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="2">Izin/Sakit Disetujui:</td>
            <td style="font-weight: bold;">{{ $summary['approved_leaves_count'] ?? 0 }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
        <tr style="background-color: #015987; color: #ffffff;">
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">No</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Tanggal Presensi</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Nama Peserta Magang</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Jam Masuk (WIB)</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Status Kehadiran</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Catatan / Peninjau</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['attendances'] as $index => $item)
            <tr>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item['date']->format('Y-m-d') }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item['intern']->name }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item['time_in'] }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item['status'] }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item['notes'] }} {{ $item['reviewer_name'] ? '(Oleh: ' . $item['reviewer_name'] . ')' : '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; border: 1px solid #cccccc;">Tidak ada data hari kerja pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
