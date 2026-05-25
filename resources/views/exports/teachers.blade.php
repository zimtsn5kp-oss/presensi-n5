<h2>Data Guru</h2>
<table width="100%" border="1" cellspacing="0" cellpadding="6">
    <thead><tr><th>NIP</th><th>Nama</th><th>Mapel</th><th>Status</th></tr></thead>
    <tbody>@foreach($teachers as $teacher)<tr><td>{{ $teacher->nip }}</td><td>{{ $teacher->name }}</td><td>{{ $teacher->subject }}</td><td>{{ $teacher->is_active ? 'Aktif' : 'Nonaktif' }}</td></tr>@endforeach</tbody>
</table>
