@extends('layouts.app', ['title' => 'Manajemen Siswa'])
@section('content')
<div class="rounded-3xl bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><h2 class="text-lg font-bold text-emerald-950">Data Siswa</h2><p class="text-sm text-slate-500">CRUD, relasi fingerprint ID, export Excel dan PDF.</p></div>
        <div class="flex gap-2">
            <a href="{{ route('students.export.excel') }}" class="rounded-xl border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700">Excel</a>
            <a href="{{ route('students.export.pdf') }}" class="rounded-xl border border-amber-200 px-4 py-2 text-sm font-semibold text-amber-700">PDF</a>
            <a href="{{ route('students.create') }}" class="rounded-xl bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Tambah</a>
        </div>
    </div>
    <form id="syncStudentsForm" method="post" action="{{ route('students.sync-to-machine') }}" class="mt-6 rounded-3xl border border-emerald-100 bg-emerald-50/60 p-4">
        @csrf
        <div class="flex flex-wrap items-end gap-3">
            <label class="min-w-72 flex-1 text-sm font-semibold text-emerald-950">Mesin Fingerprint Tujuan
                <select name="fingerprint_id" class="mt-2 w-full rounded-2xl border border-emerald-100 bg-white px-4 py-3">
                    <option value="">Pilih mesin tujuan</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}">{{ $machine->name }} - {{ $machine->ip_address }}:{{ $machine->port }}</option>
                    @endforeach
                </select>
            </label>
            <button class="rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-bold text-white shadow hover:bg-emerald-800" onclick="return confirm('Kirim siswa yang dicentang ke mesin fingerprint?')">Kirim Siswa Terpilih</button>
        </div>
        <p class="mt-2 text-xs text-slate-500">Centang siswa di tabel. Jika Fingerprint ID kosong, sistem otomatis membuat nomor user/PIN berikutnya sebelum dikirim ke mesin.</p>
    </form>
    <div class="mt-6 overflow-x-auto"><table id="studentsTable" class="w-full text-sm"><thead><tr><th><input id="checkAllStudents" type="checkbox" class="h-4 w-4 rounded border-emerald-300"></th><th>NISN</th><th>Nama</th><th>Kelas</th><th>JK</th><th>FP ID</th><th>Status</th><th>Aksi</th></tr></thead></table></div>
</div>
@endsection
@push('scripts')
<script>
new DataTable('#studentsTable',{processing:true,serverSide:true,ajax:'{{ route('students.data') }}',columns:[
 {data:'select',orderable:false,searchable:false},{data:'nisn'},{data:'name'},{data:'classroom'},{data:'gender'},{data:'fingerprint_id'},{data:'status_badge',orderable:false,searchable:false},{data:'actions',orderable:false,searchable:false}
]});
document.getElementById('checkAllStudents').addEventListener('change', function () {
    document.querySelectorAll('.student-check').forEach((checkbox) => checkbox.checked = this.checked);
});
</script>
@endpush
