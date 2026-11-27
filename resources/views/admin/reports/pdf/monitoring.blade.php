@extends('admin.reports.pdf.layout')

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center" rowspan="2">No</th>
                <th style="width: 28%;" rowspan="2">Nama Peserta Magang</th>
                <th style="width: 27%; text-align: center;" colspan="3">Presensi ({{ $metadata['working_days_count'] ?? 0 }} HK)</th>
                <th style="width: 13%;" class="text-center" rowspan="2">Logbook Manual</th>
                <th style="width: 27%; text-align: center;" colspan="3">Tugas Magang</th>
            </tr>
            <tr>
                <th style="width: 9%;" class="text-center">Hadir</th>
                <th style="width: 9%;" class="text-center">Izin</th>
                <th style="width: 9%;" class="text-center">Sakit</th>
                <th style="width: 9%;" class="text-center">Total</th>
                <th style="width: 9%;" class="text-center">Selesai</th>
                <th style="width: 9%;" class="text-center">Overdue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['intern_summaries'] as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $row['intern']->name }}</strong></td>
                    <td class="text-center" style="font-weight: bold; color: #166534;">{{ $row['valid_presence_count'] }}</td>
                    <td class="text-center" style="color: #0284c7;">{{ $row['approved_leave_days'] }} h</td>
                    <td class="text-center" style="color: #d97706;">{{ $row['approved_sick_days'] }} h</td>
                    <td class="text-center" style="font-weight: bold; color: #0f172a;">{{ $row['logbooks_total'] }}</td>
                    <td class="text-center">{{ $row['tasks_total'] }}</td>
                    <td class="text-center" style="color: #166534; font-weight: bold;">{{ $row['tasks_accepted'] }}</td>
                    <td class="text-center" style="color: {{ $row['tasks_overdue'] > 0 ? '#dc2626' : '#64748b' }}; font-weight: {{ $row['tasks_overdue'] > 0 ? 'bold' : 'normal' }};">
                        {{ $row['tasks_overdue'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 16px; color: #64748b;">
                        Tidak ada data peserta magang yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
