@extends('layouts.app', ['title' => $title])

@section('content')
<section class="rounded-3xl bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-emerald-950">{{ $title }}</h2>
            <p class="text-sm text-slate-500">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Kembali ke Dashboard</a>
    </div>

    <div class="mt-6 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b text-emerald-800">
                    <th class="py-3">NISN</th>
                    <th>Nama Murid</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th>Jam Masuk</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-slate-100">
                        <td class="py-3">{{ $row['student']->nisn }}</td>
                        <td class="font-semibold">{{ $row['student']->name }}</td>
                        <td>{{ $row['student']->classroom }}</td>
                        <td>{{ $row['status'] }}</td>
                        <td>{{ $row['check_in_at'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-500">Tidak ada data murid.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
