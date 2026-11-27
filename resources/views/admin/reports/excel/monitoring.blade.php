<table>
    <thead>
        <tr>
            <th colspan="9" style="font-size: 14pt; font-weight: bold; color: #015987;">WANTRACE - PT WAN TEKNOLOGI INTERNASIONAL</th>
        </tr>
        <tr>
            <th colspan="9" style="font-size: 12pt; font-weight: bold;">{{ $metadata['report_title'] ?? 'Rekapitulasi Monitoring Evaluasi Peserta Magang' }}</th>
        </tr>
        <tr>
            <th colspan="9">Periode: {{ \Illuminate\Support\Carbon::parse($metadata['start_date'])->translatedFormat('d F Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($metadata['end_date'])->translatedFormat('d F Y') }} ({{ $metadata['working_days_count'] ?? 0 }} Hari Kerja Efektif)</th>
        </tr>
        <tr>
            <th colspan="9">Cakupan Peserta: {{ $metadata['intern_scope_label'] ?? 'Semua Peserta' }}</th>
        </tr>
        <tr>
            <th colspan="9">Waktu Cetak: {{ $metadata['generated_at']?->translatedFormat('d F Y, H:i') ?? now()->translatedFormat('d F Y, H:i') }} WIB</th>
        </tr>
        <tr>
            <th colspan="9"></th>
        </tr>
        <tr style="background-color: #015987; color: #ffffff;">
            <th rowspan="2" style="font-weight: bold; border: 1px solid #000000; text-align: center;">No</th>
            <th rowspan="2" style="font-weight: bold; border: 1px solid #000000;">Nama Peserta Magang</th>
            <th colspan="3" style="font-weight: bold; border: 1px solid #000000; text-align: center;">Presensi ({{ $metadata['working_days_count'] ?? 0 }} Hari Kerja)</th>
            <th rowspan="2" style="font-weight: bold; border: 1px solid #000000; text-align: center;">Logbook Manual</th>
            <th colspan="3" style="font-weight: bold; border: 1px solid #000000; text-align: center;">Tugas Magang</th>
        </tr>
        <tr style="background-color: #014467; color: #ffffff;">
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">Hadir</th>
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">Izin (Hari)</th>
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">Sakit (Hari)</th>
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">Total</th>
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">Accepted</th>
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">Overdue</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['intern_summaries'] as $index => $row)
            <tr>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $index + 1 }}</td>
                <td style="font-weight: bold; border: 1px solid #cccccc;">{{ $row['intern']->name }}</td>
                <td style="text-align: center; border: 1px solid #cccccc; font-weight: bold;">{{ $row['valid_presence_count'] }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $row['approved_leave_days'] }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $row['approved_sick_days'] }}</td>
                <td style="text-align: center; border: 1px solid #cccccc; font-weight: bold;">{{ $row['logbooks_total'] }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $row['tasks_total'] }}</td>
                <td style="text-align: center; border: 1px solid #cccccc; font-weight: bold;">{{ $row['tasks_accepted'] }}</td>
                <td style="text-align: center; border: 1px solid #cccccc; color: {{ $row['tasks_overdue'] > 0 ? 'red' : 'inherit' }}; font-weight: {{ $row['tasks_overdue'] > 0 ? 'bold' : 'normal' }};">{{ $row['tasks_overdue'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; border: 1px solid #cccccc;">Tidak ada data peserta magang yang ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
</table>
