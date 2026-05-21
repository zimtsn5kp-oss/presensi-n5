@extends('layouts.app', ['title' => $student->exists ? 'Edit Siswa' : 'Tambah Siswa'])
@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $student->exists ? route('students.update', $student) : route('students.store') }}" class="max-w-4xl rounded-3xl bg-white p-6 shadow-sm">
    @csrf @if($student->exists) @method('PUT') @endif
    <div class="grid gap-5 md:grid-cols-2">
        <label class="text-sm font-semibold">NISN<input name="nisn" value="{{ old('nisn',$student->nisn) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Nama<input name="name" value="{{ old('name',$student->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Kelas<input name="classroom" value="{{ old('classroom',$student->classroom) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Jenis Kelamin<select name="gender" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"><option value="L" @selected(old('gender',$student->gender)==='L')>Laki-laki</option><option value="P" @selected(old('gender',$student->gender)==='P')>Perempuan</option></select></label>
        <label class="text-sm font-semibold">Fingerprint ID<input name="fingerprint_id" value="{{ old('fingerprint_id',$student->fingerprint_id) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Foto<input type="file" name="photo" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="md:col-span-2 text-sm font-semibold">Alamat<textarea name="address" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('address',$student->address) }}</textarea></label>
        <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$student->is_active ?? true))> Aktif</label>
    </div>
    <div class="mt-6 flex gap-3"><button class="rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan</button><a href="{{ route('students.index') }}" class="rounded-xl border px-5 py-3 font-semibold">Batal</a></div>
</form>
@endsection
