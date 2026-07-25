@extends('layouts.app', ['title' => 'Rekap Bulanan'])

@section('content')
<form class="grid gap-4 rounded-3xl bg-white p-5 shadow-sm md:grid-cols-[1fr_1fr_180px_auto]">
    <label class="text-sm font-semibold text-emerald-950">Pilih Kelas
        <select name="classroom" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            <option value="">Semua Kelas</option>
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom }}" @selected($filters['classroom']===$classroom)>{{ $classroom }}</option>
            @endforeach
        </select>
    </label>
    <label class="text-sm font-semibold text-emerald-950">Pilih Bulan
        <select name="month" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            @foreach($months as $number => $month)
                <option value="{{ $number }}" @selected($filters['month']===$number)>{{ $month }}</option>
            @endforeach
        </select>
    </label>
    <label class="text-sm font-semibold text-emerald-950">Tahun
        <input type="number" name="year" value="{{ $filters['year'] }}" min="2020" max="2100" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
    </label>
    <div class="flex items-end">
        <button class="w-full rounded-2xl bg-emerald-900 px-6 py-3 font-semibold text-white shadow hover:bg-emerald-800">Tampilkan</button>
    </div>
</form>

<section class="mt-6 overflow-hidden rounded-3xl bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
        <h2 class="font-bold text-emerald-950">Rekap: {{ $title }} - {{ $filters['classroom'] ?: 'Semua Kelas' }}</h2>
        <div class="flex flex-wrap gap-3 text-xs font-semibold">
            <span class="text-emerald-700">H Hadir</span>
            <span class="text-amber-700">T Terlambat</span>
            <span class="text-blue-700">I Izin</span>
            <span class="text-orange-700">S Sakit</span>
            <span class="text-red-700">A Alfa</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-max border-collapse text-center text-sm">
            <thead>
                <tr class="bg-slate-50 text-slate-600">
                    <th rowspan="2" class="sticky left-0 z-10 w-14 border border-slate-200 bg-slate-50 px-3 py-3">No</th>
                    <th rowspan="2" class="sticky left-14 z-10 min-w-56 border border-slate-200 bg-slate-50 px-3 py-3 text-left">Nama Murid</th>
                    <th colspan="{{ $dates->count() }}" class="border border-slate-200 px-3 py-2">Tanggal</th>
                    <th colspan="4" class="border border-slate-200 px-3 py-2">Hasil</th>
                </tr>
                <tr class="bg-slate-50 text-slate-600">
                    @foreach($dates as $date)
                        <th title="{{ $date['holiday_name'] }}" class="w-9 border border-slate-200 px-2 py-2 {{ $date['is_weekend'] ? 'bg-red-50 text-red-600' : ($date['is_holiday'] ? 'bg-amber-50 text-amber-700' : '') }}">{{ $date['date']->day }}</th>
                    @endforeach
                    <th class="w-12 border border-slate-200 bg-emerald-50 px-2 py-2 text-emerald-700">H</th>
                    <th class="w-12 border border-slate-200 bg-orange-50 px-2 py-2 text-orange-700">S</th>
                    <th class="w-12 border border-slate-200 bg-blue-50 px-2 py-2 text-blue-700">I</th>
                    <th class="w-12 border border-slate-200 bg-red-50 px-2 py-2 text-red-700">A</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $row)
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-3">{{ $index + 1 }}</td>
                        <td class="sticky left-14 z-10 border border-slate-200 bg-white px-3 py-3 text-left">
                            <strong class="block text-slate-900">{{ $row['student']->name }}</strong>
                            <span class="text-xs text-slate-500">NISN: {{ $row['student']->nisn }} | {{ $row['student']->classroom }}</span>
                        </td>
                        @foreach($dates as $date)
                            @php($status = $row['days'][$date['key']])
                            <td title="{{ $date['holiday_name'] }}" class="border border-slate-200 px-2 py-3 font-bold {{ $date['is_weekend'] ? 'bg-red-50' : ($date['is_holiday'] ? 'bg-amber-50' : '') }}">
                                @if($status === 'hadir')
                                    <span class="text-emerald-700">H</span>
                                @elseif($status === 'terlambat')
                                    <span class="text-amber-700">T</span>
                                @elseif($status === 'izin')
                                    <span class="text-blue-700">I</span>
                                @elseif($status === 'sakit')
                                    <span class="text-orange-700">S</span>
                                @elseif($status === 'alfa')
                                    <span class="text-red-700">A</span>
                                @elseif($date['is_holiday'])
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="border border-slate-200 bg-emerald-50 px-2 py-3 font-bold text-emerald-700">{{ $row['summary']['hadir'] }}</td>
                        <td class="border border-slate-200 bg-orange-50 px-2 py-3 font-bold text-orange-700">{{ $row['summary']['sakit'] }}</td>
                        <td class="border border-slate-200 bg-blue-50 px-2 py-3 font-bold text-blue-700">{{ $row['summary']['izin'] }}</td>
                        <td class="border border-slate-200 bg-red-50 px-2 py-3 font-bold text-red-700">{{ $row['summary']['alfa'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $dates->count() + 6 }}" class="px-4 py-10 text-center text-slate-500">Tidak ada murid pada kelas yang dipilih.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
