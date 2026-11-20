@extends('admin.reports.pdf.layout')

@section('content')
    <table class="summary-boxes" cellpadding="0" cellspacing="4">
        <tr>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Hari Kerja Efektif</div>
                <div class="summary-card-val">{{ $summary['working_days_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Hadir Sah</div>
                <div class="summary-card-val" style="color: #166534;">{{ $summary['total_valid_presence'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Tepat Waktu</div>
                <div class="summary-card-val" style="color: #0284c7;">{{ $summary['on_time_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Terlambat</div>
                <div class="summary-card-val" style="color: #d97706;">{{ $summary['late_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Ditolak HR</div>
                <div class="summary-card-val" style="color: #dc2626;">{{ $summary['rejected_count'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 14%;">Tanggal</th>
                <th style="width: 22%;">Nama Peserta</th>
                <th style="width: 12%;">Jam Masuk</th>
                <th style="width: 14%;">Status Presensi</th>
                <th style="width: 14%;">Verifikasi HR</th>
                <th style="width: 20%;">Catatan / Reviewer</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['attendances'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->attendance_date?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td><strong>{{ $item->intern?->name ?? 'Peserta #' . $item->intern_user_id }}</strong></td>
                    <td>{{ $item->checked_in_at?->format('H:i:s') ?? '-' }} WIB</td>
                    <td>
                        @if($item->isOnTime())
                            <span class="badge badge-success">Tepat Waktu</span>
                        @elseif($item->isLate())
                            <span class="badge badge-warning">Terlambat</span>
                        @else
                            <span class="badge badge-neutral">{{ $item->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if($item->verification_status === 'verified')
                            <span class="badge badge-success">Terverifikasi</span>
                        @elseif($item->verification_status === 'rejected')
                            <span class="badge badge-danger">Ditolak</span>
                        @else
                            <span class="badge badge-info">Submitted</span>
                        @endif
                    </td>
                    <td>
                        <div>{{ $item->notes ?: '-' }}</div>
                        @if($item->reviewer)
                            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">Oleh: {{ $item->reviewer->name }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada catatan presensi pada periode dan filter terpilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
