@extends('layouts.app', ['title' => 'Laporan Absensi'])
@section('content')
<section class="rounded-2xl bg-white p-4 shadow-sm sm:p-6">
    <form class="grid gap-3 md:grid-cols-5">
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-2xl border px-4 py-3">
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-2xl border px-4 py-3">
        <select name="status" class="rounded-2xl border px-4 py-3"><option value="">Semua Status</option>@foreach(['hadir','terlambat','izin','sakit','alfa'] as $status)<option @selected(($filters['status'] ?? '')===$status)>{{ $status }}</option>@endforeach</select>
        <button class="rounded-2xl bg-emerald-900 px-4 py-3 font-semibold text-white">Filter</button>
        <div class="grid gap-2 sm:flex"><a href="{{ route('reports.excel', request()->query()) }}" class="rounded-2xl border px-4 py-3 text-center font-semibold text-emerald-700">Excel</a><a href="{{ route('reports.pdf', request()->query()) }}" class="rounded-2xl border px-4 py-3 text-center font-semibold text-amber-700">PDF</a></div>
    </form>
    <div class="mt-6 overflow-x-auto"><table class="min-w-[640px] w-full text-left text-sm"><thead><tr class="text-emerald-800"><th class="py-3">Tanggal</th><th>Nama</th><th>Masuk</th><th>Status</th><th>Sumber</th></tr></thead><tbody>
        @forelse($logs as $log)<tr class="border-t"><td class="py-3">{{ $log->attendance_date->format('d/m/Y') }}</td><td>{{ $log->attendable?->name ?? 'Tidak dikenali' }}</td><td>{{ $log->check_in_at ?? '-' }}</td><td>{{ ucfirst($log->status) }}</td><td>{{ $log->source }}</td></tr>@empty
        <tr><td colspan="5" class="py-10 text-center text-slate-500">Tidak ada data laporan.</td></tr>@endforelse
    </tbody></table></div>
    <div class="mt-5">{{ $logs->links() }}</div>
</section>
@endsection
