@extends('layouts.app', ['title' => 'Manajemen Guru'])
@section('content')
<div class="rounded-3xl bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><h2 class="text-lg font-bold text-emerald-950">Data Guru</h2><p class="text-sm text-slate-500">NIP, mapel, jabatan, nomor fingerprint, dan status aktif.</p></div>
        <div class="flex gap-2"><a href="{{ route('teachers.export.excel') }}" class="rounded-xl border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700">Excel</a><a href="{{ route('teachers.export.pdf') }}" class="rounded-xl border border-amber-200 px-4 py-2 text-sm font-semibold text-amber-700">PDF</a><a href="{{ route('teachers.create') }}" class="rounded-xl bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Tambah</a></div>
    </div>
    <div class="mt-6 overflow-x-auto"><table id="teachersTable" class="w-full text-sm"><thead><tr><th>NIP</th><th>Nama</th><th>Mapel</th><th>Jabatan</th><th>FP ID</th><th>Status</th><th>Aksi</th></tr></thead></table></div>
</div>
@endsection
@push('scripts')
<script>
new DataTable('#teachersTable',{processing:true,serverSide:true,ajax:'{{ route('teachers.data') }}',columns:[
 {data:'nip'},{data:'name'},{data:'subject'},{data:'position'},{data:'fingerprint_id'},{data:'status_badge',orderable:false,searchable:false},{data:'actions',orderable:false,searchable:false}
]});
</script>
@endpush
