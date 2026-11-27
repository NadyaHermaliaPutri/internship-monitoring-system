@extends('admin.reports.pdf.layout')

@section('content')
    <table class="summary-boxes" cellpadding="0" cellspacing="5">
        <tr>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Hari Kerja Efektif</div>
                <div class="summary-card-val" style="color: #015987;">{{ $summary['working_days_count'] ?? 0 }} HK</div>
            </td>
            <td class="summary-card" style="width: 20%; border-top-color: #16a34a;">
                <div class="summary-card-title">Total Hadir</div>
                <div class="summary-card-val" style="color: #166534;">{{ $summary['total_valid_presence'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%; border-top-color: #0284c7;">
                <div class="summary-card-title">Tepat Waktu</div>
                <div class="summary-card-val" style="color: #0284c7;">{{ $summary['on_time_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%; border-top-color: #d97706;">
                <div class="summary-card-title">Terlambat</div>
                <div class="summary-card-val" style="color: #d97706;">{{ $summary['late_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%; border-top-color: #dc2626;">
                <div class="summary-card-title">Tidak Hadir</div>
                <div class="summary-card-val" style="color: #dc2626;">{{ $summary['absent_count'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 15%;" class="text-center">Tanggal</th>
                <th style="width: 25%;">Nama Peserta</th>
                <th style="width: 14%;" class="text-center">Jam Masuk</th>
                <th style="width: 16%;" class="text-center">Status Kehadiran</th>
                <th style="width: 25%;">Catatan / Reviewer</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['attendances'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item['date']->translatedFormat('d M Y') }}</td>
                    <td><strong>{{ $item['intern']->name }}</strong></td>
                    <td class="text-center">{{ $item['time_in'] }}</td>
                    <td class="text-center">
                        @if($item['status'] === 'Hadir')
                            <span class="badge badge-success">Hadir</span>
                        @elseif($item['status'] === 'Terlambat')
                            <span class="badge badge-warning">Terlambat</span>
                        @elseif(in_array($item['status'], ['Izin', 'Sakit'], true))
                            <span class="badge badge-info">{{ $item['status'] }}</span>
                        @else
                            <span class="badge badge-danger">{{ $item['status'] }}</span>
                        @endif
                    </td>
                    <td>
                        <div>{{ $item['notes'] }}</div>
                        @if($item['reviewer_name'])
                            <div style="font-size: 7px; color: #64748b; margin-top: 1px;">Oleh: {{ $item['reviewer_name'] }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 16px; color: #64748b;">
                        Tidak ada catatan hari kerja pada periode dan filter terpilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
