@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="space-y-6">
    <section class="overflow-hidden rounded-2xl bg-emerald-950 text-white shadow-sm">
        <div class="grid gap-4 p-5 lg:grid-cols-[1fr_280px] lg:p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.22em] text-emerald-200">Prisma</p>
                <h2 class="mt-2 text-2xl font-black leading-tight">Dashboard Presensi Murid</h2>
                <p class="mt-2 max-w-2xl text-sm leading-5 text-emerald-100">
                    Pantau ringkasan presensi hari ini, status setiap kelas, dan murid yang perlu ditindaklanjuti.
                </p>
            </div>
            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                <p id="clockDay" class="text-xs font-semibold uppercase tracking-[.18em] text-emerald-200">{{ now()->isoFormat('dddd') }}</p>
                <p id="clockDate" class="mt-1 text-lg font-bold">{{ now()->isoFormat('D MMMM YYYY') }}</p>
                <p id="clockTime" class="mt-3 text-4xl font-black tabular-nums">{{ now()->format('H:i:s') }}</p>
            </div>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['Total Murid', $metrics['total_students'], 'bg-teal-500', null],
            ['Hadir', $metrics['student_present'], 'bg-emerald-500', null],
            ['Terlambat', $metrics['late'], 'bg-amber-500', 'late'],
            ['Tidak Hadir', $metrics['absent'], 'bg-red-500', 'absent'],
            ['Belum Absen', $metrics['not_checked_in'], 'bg-slate-600', 'not_checked_in'],
        ] as [$label, $value, $color, $detail])
            @if($detail)
                <a href="{{ route('dashboard.details', $detail) }}" target="_blank" rel="noopener" class="group rounded-2xl border border-white bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="h-8 w-8 rounded-xl {{ $color }} shadow-sm"></div>
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-50 text-lg text-slate-400 transition group-hover:bg-emerald-50 group-hover:text-emerald-800">&rsaquo;</span>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-3xl font-black text-emerald-950">{{ $value }}</p>
                </a>
            @else
                <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                    <div class="h-8 w-8 rounded-xl {{ $color }} shadow-sm"></div>
                    <p class="mt-4 text-sm font-semibold text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-3xl font-black text-emerald-950">{{ $value }}</p>
                </div>
            @endif
        @endforeach
    </div>

    <section class="rounded-3xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-emerald-950">Kehadiran Hari Ini</h2>
                <p class="text-sm text-slate-500">{{ $metrics['student_present'] }} dari {{ $metrics['total_students'] }} murid sudah tercatat hadir.</p>
            </div>
            <p class="text-4xl font-black text-emerald-950">{{ $attendancePercentage }}%</p>
        </div>
        <div class="mt-5 h-4 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 via-teal-400 to-amber-300" style="width: {{ min($attendancePercentage, 100) }}%"></div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-emerald-950">Status Presensi</h2>
                <p class="text-sm text-slate-500">Ringkasan presensi hari ini berdasarkan kelas.</p>
            </div>
            <form class="flex w-full items-end gap-3 sm:w-auto">
                <label class="min-w-56 flex-1 text-sm font-semibold text-emerald-950">Filter Kelas
                    <select name="classroom" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <option value="">Semua Kelas</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom }}" @selected($selectedClassroom===$classroom)>{{ $classroom }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="rounded-2xl bg-emerald-900 px-5 py-3 font-semibold text-white shadow hover:bg-emerald-800">Tampilkan</button>
            </form>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-emerald-800">
                        <th class="py-3">Kelas</th>
                        <th>Murid</th>
                        <th>Hadir</th>
                        <th>Terlambat</th>
                        <th>Tidak Hadir</th>
                        <th>Belum Absen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statusRows as $row)
                        <tr class="border-b border-slate-100">
                            <td class="py-4 font-black text-emerald-950">{{ $row['classroom'] }}</td>
                            <td>{{ $row['total'] }}</td>
                            <td><span class="rounded-full bg-emerald-50 px-3 py-1 font-bold text-emerald-700">{{ $row['present'] }}</span></td>
                            <td><span class="rounded-full bg-amber-50 px-3 py-1 font-bold text-amber-700">{{ $row['late'] }}</span></td>
                            <td><span class="rounded-full bg-red-50 px-3 py-1 font-bold text-red-700">{{ $row['absent'] }}</span></td>
                            <td><span class="rounded-full bg-slate-100 px-3 py-1 font-bold text-slate-700">{{ $row['not_checked_in'] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-slate-500">Tidak ada data murid pada kelas yang dipilih.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function updateDashboardClock() {
    const formatterDay = new Intl.DateTimeFormat('id-ID', {weekday: 'long'});
    const formatterDate = new Intl.DateTimeFormat('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});
    const formatterTime = new Intl.DateTimeFormat('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false});
    const now = new Date();

    document.getElementById('clockDay').textContent = formatterDay.format(now);
    document.getElementById('clockDate').textContent = formatterDate.format(now);
    document.getElementById('clockTime').textContent = formatterTime.format(now).replaceAll('.', ':');
}

updateDashboardClock();
setInterval(updateDashboardClock, 1000);
setTimeout(() => location.reload(), 300000);
</script>
@endpush
