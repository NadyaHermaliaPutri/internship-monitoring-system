@extends('admin.reports.pdf.layout')

@section('content')
    <div style="background-color: #f1f5f9; padding: 6px 10px; margin-bottom: 12px; border-left: 3px solid #015987; font-size: 9px; color: #475569;">
        <strong>Catatan Evaluasi HR:</strong> Rekapitulasi di bawah ini merangkum data faktual aktivitas peserta magang selama periode laporan ({{ $metadata['working_days_count'] ?? 0 }} hari kerja). Rekap ini murni data pendukung evaluasi dan tidak memuat formula nilai akhir otomatis.
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center" rowspan="2">No</th>
                <th style="width: 17%;" rowspan="2">Nama Peserta Magang</th>
                <th style="width: 7%;" class="text-center" rowspan="2">Status Akun</th>
                <th style="width: 28%; text-align: center;" colspan="4">Presensi ({{ $metadata['working_days_count'] ?? 0 }} HK)</th>
                <th style="width: 9%;" class="text-center" rowspan="2">Izin/Sakit (Hari)</th>
                <th style="width: 16%; text-align: center;" colspan="2">Logbook Manual</th>
                <th style="width: 20%; text-align: center;" colspan="3">Tugas Magang</th>
            </tr>
            <tr>
                <th style="width: 7%;" class="text-center">Sah</th>
                <th style="width: 7%;" class="text-center">Tepat</th>
                <th style="width: 7%;" class="text-center">Telat</th>
                <th style="width: 7%;" class="text-center">Tolak</th>
                <th style="width: 8%;" class="text-center">Total</th>
                <th style="width: 8%;" class="text-center">Appr</th>
                <th style="width: 7%;" class="text-center">Total</th>
                <th style="width: 7%;" class="text-center">Selesai</th>
                <th style="width: 6%;" class="text-center">Overdue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['intern_summaries'] as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $row['intern']->name }}</strong></td>
                    <td class="text-center">
                        @if($row['is_active'])
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-neutral">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-weight: bold; color: #166534;">{{ $row['valid_presence_count'] }}</td>
                    <td class="text-center" style="color: #0284c7;">{{ $row['on_time_count'] }}</td>
                    <td class="text-center" style="color: #d97706;">{{ $row['late_count'] }}</td>
                    <td class="text-center" style="color: #dc2626;">{{ $row['rejected_count'] }}</td>
                    <td class="text-center">{{ $row['approved_leave_days'] }}</td>
                    <td class="text-center">{{ $row['logbooks_total'] }}</td>
                    <td class="text-center" style="color: #166534; font-weight: bold;">{{ $row['logbooks_approved'] }}</td>
                    <td class="text-center">{{ $row['tasks_total'] }}</td>
                    <td class="text-center" style="color: #166534; font-weight: bold;">{{ $row['tasks_accepted'] }}</td>
                    <td class="text-center" style="color: {{ $row['tasks_overdue'] > 0 ? '#dc2626' : '#64748b' }}; font-weight: {{ $row['tasks_overdue'] > 0 ? 'bold' : 'normal' }};">
                        {{ $row['tasks_overdue'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada data peserta magang yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
