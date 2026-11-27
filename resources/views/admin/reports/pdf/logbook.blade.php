@extends('admin.reports.pdf.layout')

@section('content')
    <table class="summary-boxes" cellpadding="0" cellspacing="5">
        <tr>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-card-title">Total Aktivitas &amp; Tugas</div>
                <div class="summary-card-val" style="color: #015987;">{{ $summary['total_items'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 25%; border-top-color: #0284c7;">
                <div class="summary-card-title">Logbook Manual</div>
                <div class="summary-card-val" style="color: #0284c7;">{{ $summary['manual_logbooks_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 25%; border-top-color: #6366f1;">
                <div class="summary-card-title">Tugas dari PIC</div>
                <div class="summary-card-val" style="color: #4f46e5;">{{ $summary['tasks_count'] ?? 0 }}</div>
            </td>
            <td class="summary-card" style="width: 25%; border-top-color: #16a34a;">
                <div class="summary-card-title">Tugas Selesai (Accepted)</div>
                <div class="summary-card-val" style="color: #166534;">{{ $summary['tasks_accepted_count'] ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 14%;" class="text-center">Tanggal</th>
                <th style="width: 20%;">Nama Peserta</th>
                <th style="width: 11%;" class="text-center">Jenis</th>
                <th style="width: 33%;">Aktivitas / Kegiatan</th>
                <th style="width: 17%;">Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['entries'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item['date'] ? $item['date']->translatedFormat('d M Y') : '-' }}</td>
                    <td><strong>{{ $item['intern_name'] }}</strong></td>
                    <td class="text-center">
                        @if($item['source_type'] === 'manual')
                            <span class="badge badge-info">Manual</span>
                        @else
                            <span class="badge badge-warning">Tugas PIC</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #0f172a;">{{ $item['title'] }}</div>
                        @if($item['description'])
                            <div style="font-size: 7.5px; color: #475569; margin-top: 1.5px; line-height: 1.35;">{{ $item['description'] }}</div>
                        @endif
                        @if($item['meta_info'])
                            <div style="font-size: 7px; color: #64748b; margin-top: 1.5px; font-weight: bold;">{{ $item['meta_info'] }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #015987;">{{ $item['pic_in_charge'] }}</div>
                        <div style="font-size: 7px; color: #64748b; margin-top: 1px;">Status: {{ $item['status_label'] }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 16px; color: #64748b;">
                        Tidak ada catatan logbook atau tugas pada periode dan filter terpilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
