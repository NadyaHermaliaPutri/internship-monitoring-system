@extends('admin.reports.pdf.layout')

@section('content')
    <table class="summary-boxes" cellpadding="0" cellspacing="4">
        <tr>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-card-title">Total Logbook Manual</div>
                <div class="summary-card-val">{{ $summary['total_entries'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-card-title">Disetujui (Approved)</div>
                <div class="summary-card-val" style="color: #166534;">{{ $summary['approved_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-card-title">Menunggu Review</div>
                <div class="summary-card-val" style="color: #d97706;">{{ $summary['pending_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-card-title">Perlu Revisi</div>
                <div class="summary-card-val" style="color: #dc2626;">{{ $summary['revision_requested_count'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 18%;">Nama Peserta</th>
                <th style="width: 22%;">Aktivitas / Kegiatan</th>
                <th style="width: 20%;">Hasil / Pencapaian</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 12%;">Peninjau</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['logbooks'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->log_date?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td>
                        <strong>{{ $item->intern?->name ?? 'Peserta #' . $item->intern_user_id }}</strong>
                        @if(($item->revisions_count ?? 0) > 0)
                            <div style="font-size: 8px; color: #d97706; margin-top: 2px;">{{ $item->revisions_count }}x revisi</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #0f172a;">{{ $item->title }}</div>
                        <div style="font-size: 9px; color: #475569; margin-top: 2px;">{{ \Illuminate\Support\Str::limit($item->activity_description, 100) }}</div>
                    </td>
                    <td>
                        <div style="font-size: 9px; color: #334155;">{{ \Illuminate\Support\Str::limit($item->result, 80) ?: '-' }}</div>
                    </td>
                    <td>
                        @if($item->status?->value === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif($item->status?->value === 'revision_requested')
                            <span class="badge badge-warning">Perlu Revisi</span>
                        @else
                            <span class="badge badge-info">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($item->reviewer)
                            <div>{{ $item->reviewer->name }}</div>
                            <div style="font-size: 8px; color: #64748b;">{{ $item->reviewed_at?->translatedFormat('d/m/Y H:i') }}</div>
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada catatan logbook manual pada periode dan filter terpilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
