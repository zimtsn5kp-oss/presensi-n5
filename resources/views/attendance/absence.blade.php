@extends('layouts.app', ['title' => 'Input Ketidakhadiran'])
@section('content')
<form method="post" action="{{ route('attendance.absence.store') }}" class="max-w-3xl rounded-3xl bg-white p-6 shadow-sm">@csrf
    <div class="grid gap-5 md:grid-cols-2">
        <label class="text-sm font-semibold">Nama Siswa<select name="attendable_id" class="mt-2 w-full rounded-2xl border px-4 py-3">@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->name }} - {{ $student->classroom }}</option>@endforeach</select></label>
        <label class="text-sm font-semibold">Tanggal<input type="date" name="absence_date" value="{{ now()->toDateString() }}" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
        <label class="text-sm font-semibold">Status<select name="type" class="mt-2 w-full rounded-2xl border px-4 py-3"><option value="izin">Izin</option><option value="sakit">Sakit</option><option value="alfa">Alfa</option></select></label>
        <label class="md:col-span-2 text-sm font-semibold">Keterangan<textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border px-4 py-3"></textarea></label>
    </div>
    <button class="mt-6 rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan Ketidakhadiran</button>
</form>
@endsection
