@extends('layouts.app', ['title' => $student->exists ? 'Edit Siswa' : 'Tambah Siswa'])
@section('content')
<form method="post" action="{{ $student->exists ? route('students.update', $student) : route('students.store') }}" class="max-w-4xl rounded-3xl bg-white p-6 shadow-sm">
    @csrf @if($student->exists) @method('PUT') @endif
    <div class="grid gap-5 md:grid-cols-2">
        <label class="text-sm font-semibold">NISN<input name="nisn" value="{{ old('nisn',$student->nisn) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Nama<input name="name" value="{{ old('name',$student->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Kelas<select name="classroom" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom }}" @selected(old('classroom',$student->classroom)===$classroom)>{{ $classroom }}</option>
            @endforeach
        </select></label>
        <label class="text-sm font-semibold">Jenis Kelamin<select name="gender" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"><option value="L" @selected(old('gender',$student->gender)==='L')>Laki-laki</option><option value="P" @selected(old('gender',$student->gender)==='P')>Perempuan</option></select></label>
        <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$student->is_active ?? true))> Aktif</label>
    </div>
    <div class="mt-6 flex gap-3"><button class="rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan</button><a href="{{ route('students.index') }}" class="rounded-xl border px-5 py-3 font-semibold">Batal</a></div>
</form>
@unless($student->exists)
    <section class="mt-6 max-w-4xl rounded-3xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-emerald-950">Import Siswa dari Excel</h2>
                <p class="text-sm text-slate-500">Format kolom: nisn, nama, kelas, jk, aktif. Kelas: 7A sampai 9C. JK: L atau P. Aktif: 1/0.</p>
            </div>
            <a href="{{ route('students.import.template') }}" class="rounded-xl border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700">Unduh Format</a>
        </div>
        <form method="post" enctype="multipart/form-data" action="{{ route('students.import') }}" class="mt-5 flex flex-wrap items-end gap-3">
            @csrf
            <label class="min-w-72 flex-1 text-sm font-semibold">File Excel
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            </label>
            <button class="rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Import</button>
        </form>
    </section>
@endunless
@endsection
