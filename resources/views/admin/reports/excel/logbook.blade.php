<table>
    <thead>
        <tr>
            <th colspan="7" style="font-size: 14pt; font-weight: bold; color: #015987;">WANTRACE - PT WAN TEKNOLOGI INTERNASIONAL</th>
        </tr>
        <tr>
            <th colspan="7" style="font-size: 12pt; font-weight: bold;">{{ $metadata['report_title'] ?? 'Rekapitulasi Logbook Manual' }}</th>
        </tr>
        <tr>
            <th colspan="7">Periode: {{ \Illuminate\Support\Carbon::parse($metadata['start_date'])->translatedFormat('d F Y') }} s.d. {{ \Illuminate\Support\Carbon::parse($metadata['end_date'])->translatedFormat('d F Y') }}</th>
        </tr>
        <tr>
            <th colspan="7">Cakupan Peserta: {{ $metadata['intern_scope_label'] ?? 'Semua Peserta' }}</th>
        </tr>
        <tr>
            <th colspan="7">Waktu Cetak: {{ $metadata['generated_at']?->translatedFormat('d F Y, H:i') ?? now()->translatedFormat('d F Y, H:i') }} WIB</th>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; background-color: #eef4f8;">RINGKASAN LOGBOOK MANUAL</th>
            <th colspan="5"></th>
        </tr>
        <tr>
            <td colspan="2">Total Catatan Manual:</td>
            <td style="font-weight: bold;">{{ $summary['total_entries'] ?? 0 }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2">Disetujui (Approved):</td>
            <td style="font-weight: bold; color: green;">{{ $summary['approved_count'] ?? 0 }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2">Menunggu Review:</td>
            <td style="font-weight: bold;">{{ $summary['pending_count'] ?? 0 }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2">Perlu Revisi:</td>
            <td style="font-weight: bold; color: red;">{{ $summary['revision_requested_count'] ?? 0 }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr style="background-color: #015987; color: #ffffff;">
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">No</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Tanggal</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Nama Peserta Magang</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Judul Aktivitas</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Uraian Kegiatan</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Hasil / Pencapaian</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Status &amp; Peninjau</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['logbooks'] as $index => $item)
            <tr>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item->log_date?->format('Y-m-d') ?? '-' }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item->intern?->name ?? 'Peserta #' . $item->intern_user_id }} {{ ($item->revisions_count ?? 0) > 0 ? "({$item->revisions_count}x revisi)" : '' }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item->title }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item->activity_description }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item->result ?: '-' }}</td>
                <td style="border: 1px solid #cccccc;">{{ strtoupper($item->status?->value ?? '-') }} {{ $item->reviewer ? '(Oleh: ' . $item->reviewer->name . ')' : '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; border: 1px solid #cccccc;">Tidak ada data logbook manual pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
