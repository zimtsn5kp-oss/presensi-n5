@extends('layouts.app', ['title' => $teacher->exists ? 'Edit Guru' : 'Tambah Guru'])
@section('content')
<form method="post" action="{{ $teacher->exists ? route('teachers.update', $teacher) : route('teachers.store') }}" class="max-w-4xl rounded-3xl bg-white p-6 shadow-sm">
    @csrf @if($teacher->exists) @method('PUT') @endif
    <div class="grid gap-5 md:grid-cols-2">
        <label class="text-sm font-semibold">NIP<input name="nip" value="{{ old('nip',$teacher->nip) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Nama<input name="name" value="{{ old('name',$teacher->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Mata Pelajaran<input name="subject" value="{{ old('subject',$teacher->subject) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Jabatan<input name="position" value="{{ old('position',$teacher->position) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Fingerprint ID<input name="fingerprint_id" value="{{ old('fingerprint_id',$teacher->fingerprint_id) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$teacher->is_active ?? true))> Aktif</label>
    </div>
    <div class="mt-6 flex gap-3"><button class="rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan</button><a href="{{ route('teachers.index') }}" class="rounded-xl border px-5 py-3 font-semibold">Batal</a></div>
</form>
@endsection
