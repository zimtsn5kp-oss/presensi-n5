@extends('layouts.app', ['title' => 'Manajemen Siswa'])
@section('content')
<div class="rounded-3xl bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><h2 class="text-lg font-bold text-emerald-950">Data Siswa</h2><p class="text-sm text-slate-500">CRUD data siswa, export Excel dan PDF.</p></div>
        <div class="flex gap-2">
            <a href="{{ route('students.export.excel') }}" class="rounded-xl border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700">Excel</a>
            <a href="{{ route('students.export.pdf') }}" class="rounded-xl border border-amber-200 px-4 py-2 text-sm font-semibold text-amber-700">PDF</a>
            <a href="{{ route('students.create') }}" class="rounded-xl bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Tambah</a>
        </div>
    </div>
    <div class="mt-6 grid gap-3 md:grid-cols-2">
        <label class="text-sm font-semibold text-emerald-950">Kelas
            <select id="classroomFilter" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <option value="">Semua kelas</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom }}">{{ $classroom }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex items-end">
            <button type="button" id="resetStudentFilters" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600">Reset Filter</button>
        </div>
    </div>
    <div class="mt-6 overflow-x-auto"><table id="studentsTable" class="w-full text-sm"><thead><tr><th>NISN</th><th>Nama</th><th>Kelas</th><th>JK</th><th>Status</th><th>Aksi</th></tr></thead></table></div>
</div>
@endsection
@push('scripts')
<script>
const studentsTable = new DataTable('#studentsTable',{processing:true,serverSide:true,ajax:{
    url: '{{ route('students.data') }}',
    data: function (data) {
        data.classroom = document.getElementById('classroomFilter').value;
    }
},columns:[
 {data:'nisn'},{data:'name'},{data:'classroom'},{data:'gender'},{data:'status_badge',orderable:false,searchable:false},{data:'actions',orderable:false,searchable:false}
]});
document.getElementById('classroomFilter').addEventListener('change', () => studentsTable.ajax.reload());
document.getElementById('resetStudentFilters').addEventListener('click', function () {
    document.getElementById('classroomFilter').value = '';
    studentsTable.ajax.reload();
});
</script>
@endpush
