@extends('admin.reports.pdf.layout')

@section('content')
    <table class="summary-boxes" cellpadding="0" cellspacing="6">
        <tr>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Total Pengajuan</div>
                <div class="summary-card-val" style="color: #015987;">{{ $summary['total_requests'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%; border-top-color: #16a34a;">
                <div class="summary-card-title">Disetujui (Approved)</div>
                <div class="summary-card-val" style="color: #166534;">{{ $summary['approved_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%; border-top-color: #d97706;">
                <div class="summary-card-title">Menunggu (Pending)</div>
                <div class="summary-card-val" style="color: #d97706;">{{ $summary['pending_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%; border-top-color: #dc2626;">
                <div class="summary-card-title">Ditolak (Rejected)</div>
                <div class="summary-card-val" style="color: #dc2626;">{{ $summary['rejected_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%; border-top-color: #0284c7;">
                <div class="summary-card-title">Izin / Sakit</div>
                <div class="summary-card-val" style="color: #0284c7;">{{ ($summary['leave_count'] ?? 0) . ' / ' . ($summary['sick_count'] ?? 0) }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 20%;">Nama Peserta</th>
                <th style="width: 10%;" class="text-center">Jenis</th>
                <th style="width: 20%;">Rentang Tanggal</th>
                <th style="width: 22%;">Alasan</th>
                <th style="width: 10%;" class="text-center">Status</th>
                <th style="width: 14%;">Peninjau</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['leave_requests'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $item->intern?->name ?? 'Peserta #' . $item->intern_user_id }}</strong></td>
                    <td class="text-center">
                        @if($item->type === 'sick')
                            <span class="badge badge-warning">Sakit</span>
                        @else
                            <span class="badge badge-info">Izin</span>
                        @endif
                    </td>
                    <td>
                        {{ $item->start_date?->translatedFormat('d M Y') }} s.d.
                        {{ $item->end_date?->translatedFormat('d M Y') }}
                    </td>
                    <td>{{ $item->reason }}</td>
                    <td class="text-center">
                        @if($item->status === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif($item->status === 'rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($item->reviewer)
                            <div>{{ $item->reviewer->name }}</div>
                            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ $item->reviewed_at?->translatedFormat('d/m/Y H:i') }}</div>
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 18px; color: #64748b;">
                        Tidak ada pengajuan izin atau sakit pada periode dan filter terpilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
