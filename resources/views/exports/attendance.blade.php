<h2>Laporan Absensi</h2>
<table width="100%" border="1" cellspacing="0" cellpadding="6">
    <thead><tr><th>Tanggal</th><th>Nama</th><th>Masuk</th><th>Status</th><th>Sumber</th></tr></thead>
    <tbody>@foreach($logs as $log)<tr><td>{{ $log->attendance_date?->format('d/m/Y') }}</td><td>{{ $log->attendable?->name ?? 'Tidak dikenali' }}</td><td>{{ $log->check_in_at }}</td><td>{{ ucfirst($log->status) }}</td><td>{{ $log->source }}</td></tr>@endforeach</tbody>
</table>
