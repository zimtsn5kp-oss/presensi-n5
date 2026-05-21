@extends('layouts.app', ['title' => 'Manajemen Hari Libur'])
@section('content')
<div class="grid gap-6 xl:grid-cols-3">
    <form method="post" action="{{ route('holidays.store') }}" class="rounded-3xl bg-white p-6 shadow-sm">@csrf
        <h2 class="font-bold text-emerald-950">Tambah Hari Libur</h2>
        <label class="mt-4 block text-sm font-semibold">Nama<input name="name" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
        <label class="mt-4 block text-sm font-semibold">Tipe<select name="type" class="mt-2 w-full rounded-2xl border px-4 py-3"><option value="nasional">Nasional</option><option value="madrasah">Madrasah</option><option value="cuti_bersama">Cuti Bersama</option><option value="akademik">Akademik</option><option value="hari_besar_islam">Hari Besar Islam</option></select></label>
        <div class="mt-4 grid grid-cols-2 gap-3"><label class="text-sm font-semibold">Mulai<input type="date" name="start_date" class="mt-2 w-full rounded-2xl border px-4 py-3"></label><label class="text-sm font-semibold">Selesai<input type="date" name="end_date" class="mt-2 w-full rounded-2xl border px-4 py-3"></label></div>
        <label class="mt-4 block text-sm font-semibold">Keterangan<textarea name="description" class="mt-2 w-full rounded-2xl border px-4 py-3"></textarea></label>
        <button class="mt-5 rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan</button>
    </form>
    <section class="xl:col-span-2 rounded-3xl bg-white p-6 shadow-sm">
        <h2 class="font-bold text-emerald-950">Kalender Visual</h2>
        <div class="mt-5 grid gap-3 md:grid-cols-2">
            @foreach($holidays as $holiday)
                <article class="rounded-3xl border border-emerald-100 bg-emerald-50/40 p-4">
                    <div class="flex justify-between gap-4"><strong>{{ $holiday->name }}</strong><span class="text-xs font-semibold text-amber-700">{{ str_replace('_',' ', $holiday->type) }}</span></div>
                    <p class="mt-2 text-sm text-slate-500">{{ $holiday->start_date->format('d M Y') }} - {{ $holiday->end_date->format('d M Y') }}</p>
                    <form method="post" action="{{ route('holidays.destroy',$holiday) }}" class="mt-3">@csrf @method('DELETE')<button class="text-xs font-semibold text-red-600">Hapus</button></form>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
