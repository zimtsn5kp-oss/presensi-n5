@extends('layouts.app', ['title' => 'Pengaturan Sistem'])
@section('content')
<div class="grid gap-6 xl:grid-cols-2">
    <form method="post" enctype="multipart/form-data" action="{{ route('settings.school') }}" class="rounded-3xl bg-white p-6 shadow-sm">@csrf
        <h2 class="font-bold text-emerald-950">Identitas Sekolah</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <label class="text-sm font-semibold">Nama sekolah<input name="school_name" value="{{ $school->school_name }}" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Email<input name="email" value="{{ $school->email }}" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Telepon<input name="phone" value="{{ $school->phone }}" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Tahun ajaran<input name="academic_year" value="{{ $school->academic_year }}" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Semester<input name="active_semester" value="{{ $school->active_semester }}" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Logo<input type="file" name="logo" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="md:col-span-2 text-sm font-semibold">Alamat<textarea name="address" class="mt-2 w-full rounded-2xl border px-4 py-3">{{ $school->address }}</textarea></label>
        </div>
        <button class="mt-5 rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan Identitas</button>
    </form>
    <form method="post" action="{{ route('settings.attendance') }}" class="rounded-3xl bg-white p-6 shadow-sm">@csrf
        <h2 class="font-bold text-emerald-950">Jam Absensi</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <label class="text-sm font-semibold">Hari<select name="day_of_week" class="mt-2 w-full rounded-2xl border px-4 py-3"><option value="">Global</option>@foreach([1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Ahad'] as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></label>
            <label class="text-sm font-semibold">Nama shift<input name="name" value="Reguler" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Mulai masuk<input type="time" name="check_in_start" value="06:00" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Akhir masuk<input type="time" name="check_in_end" value="07:30" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Batas terlambat<input type="time" name="late_after" value="07:00" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Mulai pulang<input type="time" name="check_out_start" value="14:00" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="text-sm font-semibold">Akhir pulang<input type="time" name="check_out_end" value="16:00" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
            <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_ramadhan" value="1"> Ramadhan</label>
        </div>
        <button class="mt-5 rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan Jam</button>
    </form>
</div>
@endsection
