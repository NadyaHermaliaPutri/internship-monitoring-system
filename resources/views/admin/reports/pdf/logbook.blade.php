@extends('admin.reports.pdf.layout')

@section('content')
    <table class="summary-boxes" cellpadding="0" cellspacing="6">
        <tr>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-card-title">Total Logbook Manual</div>
                <div class="summary-card-val" style="color: #015987;">{{ $summary['total_entries'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 25%; border-top-color: #16a34a;">
                <div class="summary-card-title">Disetujui (Approved)</div>
                <div class="summary-card-val" style="color: #166534;">{{ $summary['approved_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 25%; border-top-color: #d97706;">
                <div class="summary-card-title">Menunggu Review</div>
                <div class="summary-card-val" style="color: #d97706;">{{ $summary['pending_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 25%; border-top-color: #dc2626;">
                <div class="summary-card-title">Perlu Revisi</div>
                <div class="summary-card-val" style="color: #dc2626;">{{ $summary['revision_requested_count'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 14%;" class="text-center">Tanggal</th>
                <th style="width: 22%;">Nama Peserta</th>
                <th style="width: 39%;">Aktivitas / Kegiatan</th>
                <th style="width: 20%;">Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['logbooks'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->log_date?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td>
                        <strong>{{ $item->intern?->name ?? 'Peserta #' . $item->intern_user_id }}</strong>
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #0f172a;">{{ $item->title }}</div>
                        <div style="font-size: 8.5px; color: #475569; margin-top: 2px; line-height: 1.35;">{{ $item->activity_description }}</div>
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #015987;">{{ $item->pic_in_charge ?? ($item->reviewer?->name ?? '-') }}</div>
                        @if($item->reviewer)
                            <div style="font-size: 7.5px; color: #64748b; margin-top: 1px;">Review: {{ $item->reviewed_at?->translatedFormat('d/m/Y H:i') }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 18px; color: #64748b;">
                        Tidak ada catatan logbook manual pada periode dan filter terpilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
