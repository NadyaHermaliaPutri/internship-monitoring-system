<table>
    <thead>
        <tr>
            <th colspan="7" style="font-size: 14pt; font-weight: bold; color: #015987;">WANTRACE - PT WAN TEKNOLOGI INTERNASIONAL</th>
        </tr>
        <tr>
            <th colspan="7" style="font-size: 12pt; font-weight: bold;">{{ $metadata['report_title'] ?? 'Rekapitulasi Tugas Magang' }}</th>
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
            <th colspan="2" style="font-weight: bold; background-color: #eef4f8;">RINGKASAN TUGAS</th>
            <th colspan="5"></th>
        </tr>
        <tr>
            <td colspan="2">Total Tugas:</td>
            <td style="font-weight: bold;">{{ $summary['total_tasks'] ?? 0 }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2">Selesai (Accepted):</td>
            <td style="font-weight: bold; color: green;">{{ $summary['accepted_count'] ?? 0 }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2">Sedang Dikerjakan:</td>
            <td style="font-weight: bold;">{{ $summary['in_progress_count'] ?? 0 }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2">Menunggu Review:</td>
            <td style="font-weight: bold;">{{ $summary['submitted_count'] ?? 0 }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2">Overdue (Lewat Deadline):</td>
            <td style="font-weight: bold; color: red;">{{ $summary['overdue_count'] ?? 0 }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr style="background-color: #015987; color: #ffffff;">
            <th style="font-weight: bold; border: 1px solid #000000; text-align: center;">No</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Judul Tugas</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Peserta Penerima</th>
            <th style="font-weight: bold; border: 1px solid #000000;">PIC Pemberi</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Waktu Diberikan &amp; Deadline</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Status Terkini</th>
            <th style="font-weight: bold; border: 1px solid #000000;">Waktu Selesai (Accepted)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data['tasks'] as $index => $item)
            @php $isOverdue = $item->isOverdue(); @endphp
            <tr>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item->title }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item->intern?->name ?? 'Peserta #' . $item->intern_user_id }}</td>
                <td style="border: 1px solid #cccccc;">{{ $item->pic?->name ?? 'PIC #' . $item->pic_user_id }}</td>
                <td style="border: 1px solid #cccccc;">
                    Diberikan: {{ $item->assigned_at?->format('Y-m-d H:i') ?? '-' }} | 
                    Deadline: {{ $item->deadline_at?->format('Y-m-d H:i') ?? '-' }}
                </td>
                <td style="border: 1px solid #cccccc;">
                    {{ strtoupper($item->status?->value ?? '-') }}
                    {{ $isOverdue ? ' (OVERDUE)' : '' }}
                </td>
                <td style="border: 1px solid #cccccc;">
                    {{ $item->accepted_at?->format('Y-m-d H:i') ?? '-' }}
                    {{ $item->acceptedByUser ? '(Oleh: ' . $item->acceptedByUser->name . ')' : '' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; border: 1px solid #cccccc;">Tidak ada data tugas pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
