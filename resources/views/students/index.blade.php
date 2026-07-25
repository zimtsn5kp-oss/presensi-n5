@extends('layouts.app', ['title' => 'Manajemen Murid'])
@section('content')
<div class="rounded-2xl bg-white p-4 shadow-sm sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><h2 class="text-lg font-bold text-emerald-950">Data Murid</h2><p class="text-sm text-slate-500">Daftar murid aktif dan nonaktif.</p></div>
        @if(auth()->user()->hasRole('administrator'))
        <div class="flex gap-2">
            <a href="{{ route('students.export.excel') }}" class="rounded-xl border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700">Excel</a>
            <a href="{{ route('students.export.pdf') }}" class="rounded-xl border border-amber-200 px-4 py-2 text-sm font-semibold text-amber-700">PDF</a>
            <a href="{{ route('students.create') }}" class="rounded-xl bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Tambah</a>
        </div>
        @endif
    </div>
    <div class="mt-6 grid gap-3 md:grid-cols-[1fr_auto_auto] md:items-end">
        <label class="text-sm font-semibold text-emerald-950">Kelas
            <select id="classroomFilter" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <option value="">Semua kelas</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom }}">{{ $classroom }}</option>
                @endforeach
            </select>
        </label>
        <button type="button" id="showStudentData" class="w-full rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-semibold text-white shadow hover:bg-emerald-800 md:w-auto">Tampilkan</button>
        <button type="button" id="resetStudentFilters" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 md:w-auto">Reset Filter</button>
    </div>
    <p id="studentTableHint" class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm text-slate-600">Pilih filter kelas lalu klik Tampilkan untuk memuat data murid.</p>
    <div class="mt-6 overflow-x-auto"><table id="studentsTable" class="min-w-[720px] w-full text-sm"><thead><tr><th>NISN</th><th>Nama</th><th>Kelas</th><th>JK</th><th>Status</th>@if(auth()->user()->hasRole('administrator'))<th>Aksi</th>@endif</tr></thead><tbody></tbody></table></div>
</div>
@endsection
@push('scripts')
<script>
let studentsTable = null;
const studentTableHint = document.getElementById('studentTableHint');
const studentsTableElement = document.getElementById('studentsTable');

function initStudentsTable() {
    studentTableHint.classList.add('hidden');

    if (studentsTable) {
        studentsTable.ajax.reload();
        return;
    }

    studentsTable = new DataTable('#studentsTable', {
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: '{{ route('students.data') }}',
            data: function (data) {
                data.load = 1;
                data.classroom = document.getElementById('classroomFilter').value;
            }
        },
        columns: [
            {data: 'nisn'},
            {data: 'name'},
            {data: 'classroom'},
            {data: 'gender'},
            {data: 'status_badge', orderable: false, searchable: false}
            @if(auth()->user()->hasRole('administrator')),{data: 'actions', orderable: false, searchable: false}@endif
        ],
        language: {
            emptyTable: 'Tidak ada data murid pada filter yang dipilih.'
        }
    });
}

document.getElementById('showStudentData').addEventListener('click', initStudentsTable);
document.getElementById('classroomFilter').addEventListener('change', function () {
    if (studentsTable) {
        initStudentsTable();
    }
});
document.getElementById('resetStudentFilters').addEventListener('click', function () {
    document.getElementById('classroomFilter').value = '';
    if (studentsTable) {
        studentsTable.destroy();
        studentsTable = null;
        studentsTableElement.querySelector('tbody').innerHTML = '';
    }
    studentTableHint.classList.remove('hidden');
});
</script>
@endpush
