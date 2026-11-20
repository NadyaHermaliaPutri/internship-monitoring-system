@extends('admin.reports.pdf.layout')

@section('content')
    <table class="summary-boxes" cellpadding="0" cellspacing="4">
        <tr>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Total Tugas</div>
                <div class="summary-card-val">{{ $summary['total_tasks'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Selesai (Accepted)</div>
                <div class="summary-card-val" style="color: #166534;">{{ $summary['accepted_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Sedang Dikerjakan</div>
                <div class="summary-card-val" style="color: #0284c7;">{{ $summary['in_progress_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Menunggu Review</div>
                <div class="summary-card-val" style="color: #d97706;">{{ $summary['submitted_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 20%;">
                <div class="summary-card-title">Tugas Overdue</div>
                <div class="summary-card-val" style="color: #dc2626;">{{ $summary['overdue_count'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 22%;">Judul Tugas</th>
                <th style="width: 16%;">Peserta Penerima</th>
                <th style="width: 14%;">PIC Pemberi</th>
                <th style="width: 16%;">Penugasan &amp; Tenggat</th>
                <th style="width: 14%;">Status Saat Ini</th>
                <th style="width: 14%;">Tanggal Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['tasks'] as $index => $item)
                @php
                    $isOverdue = $item->isOverdue();
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->title }}</strong>
                        @if($item->description)
                            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ \Illuminate\Support\Str::limit($item->description, 70) }}</div>
                        @endif
                    </td>
                    <td>{{ $item->intern?->name ?? 'Peserta #' . $item->intern_user_id }}</td>
                    <td>{{ $item->pic?->name ?? 'PIC #' . $item->pic_user_id }}</td>
                    <td>
                        <div style="font-size: 9px;">Diberikan: {{ $item->assigned_at?->translatedFormat('d M Y') ?? '-' }}</div>
                        <div style="font-size: 9px; font-weight: bold; color: {{ $isOverdue ? '#dc2626' : '#475569' }};">
                            Deadline: {{ $item->deadline_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                        </div>
                    </td>
                    <td>
                        @if($item->status?->value === 'accepted')
                            <span class="badge badge-success">Accepted</span>
                        @elseif($item->status?->value === 'in_progress')
                            <span class="badge badge-info">In Progress</span>
                        @elseif($item->status?->value === 'submitted')
                            <span class="badge badge-warning">Submitted</span>
                        @else
                            <span class="badge badge-neutral">{{ $item->status?->value ?? '-' }}</span>
                        @endif

                        @if($isOverdue)
                            <div style="margin-top: 3px;"><span class="badge badge-danger">OVERDUE</span></div>
                        @endif
                    </td>
                    <td>
                        @if($item->accepted_at)
                            <div>{{ $item->accepted_at->translatedFormat('d M Y, H:i') }}</div>
                            @if($item->acceptedByUser)
                                <div style="font-size: 8px; color: #64748b;">Oleh: {{ $item->acceptedByUser->name }}</div>
                            @endif
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada catatan tugas pada periode dan filter terpilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
