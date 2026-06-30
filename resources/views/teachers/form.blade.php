@extends('layouts.app', ['title' => $teacher->exists ? 'Edit Guru' : 'Tambah Guru'])
@section('content')
<form method="post" action="{{ $teacher->exists ? route('teachers.update', $teacher) : route('teachers.store') }}" class="max-w-4xl rounded-3xl bg-white p-6 shadow-sm">
    @csrf @if($teacher->exists) @method('PUT') @endif
    @unless($teacher->exists)
        <p class="mb-5 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">Akun login otomatis dibuat dengan username NIP dan password awal <strong>prisma123</strong>.</p>
    @endunless
    <div class="grid gap-5 md:grid-cols-2">
        <label class="text-sm font-semibold">NIP<input name="nip" value="{{ old('nip',$teacher->nip) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Nama<input name="name" value="{{ old('name',$teacher->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        <label class="text-sm font-semibold">Mata Pelajaran<input name="subject" value="{{ old('subject',$teacher->subject) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        @if($teacher->exists)
            <label class="text-sm font-semibold">Password Baru<input type="password" name="password" autocomplete="new-password" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Kosongkan jika tidak diubah"></label>
            <label class="text-sm font-semibold">Konfirmasi Password<input type="password" name="password_confirmation" autocomplete="new-password" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3"></label>
        @endif
        <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$teacher->is_active ?? true))> Aktif</label>
    </div>
    <div class="mt-6 flex gap-3"><button class="rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan</button><a href="{{ route('teachers.index') }}" class="rounded-xl border px-5 py-3 font-semibold">Batal</a></div>
</form>
@unless($teacher->exists)
    <section class="mt-6 max-w-4xl rounded-3xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-emerald-950">Import Guru dari Excel</h2>
                <p class="text-sm text-slate-500">Format kolom: nip, nama, mapel, aktif. Akun dibuat dengan username NIP dan password awal prisma123.</p>
            </div>
            <a href="{{ route('teachers.import.template') }}" class="rounded-xl border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-700">Unduh Format</a>
        </div>
        <form method="post" enctype="multipart/form-data" action="{{ route('teachers.import') }}" class="mt-5 flex flex-wrap items-end gap-3">
            @csrf
            <label class="min-w-72 flex-1 text-sm font-semibold">File Excel
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            </label>
            <button class="rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Import</button>
        </form>
    </section>
@endunless
@endsection
