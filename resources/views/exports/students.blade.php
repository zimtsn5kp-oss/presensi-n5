<h2>Data Murid</h2>
<table width="100%" border="1" cellspacing="0" cellpadding="6">
    <thead><tr><th>NISN</th><th>Nama</th><th>Kelas</th><th>JK</th><th>Status</th></tr></thead>
    <tbody>@foreach($students as $student)<tr><td>{{ $student->nisn }}</td><td>{{ $student->name }}</td><td>{{ $student->classroom }}</td><td>{{ $student->gender }}</td><td>{{ $student->is_active ? 'Aktif' : 'Nonaktif' }}</td></tr>@endforeach</tbody>
</table>
