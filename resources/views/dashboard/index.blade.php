@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
    @foreach([
        ['Total hadir siswa', $metrics['student_present'], 'bg-emerald-600'],
        ['Terlambat', $metrics['late'], 'bg-amber-500'],
        ['Tidak hadir', $metrics['absent'], 'bg-red-500'],
        ['Guru hadir', $metrics['teacher_present'], 'bg-teal-600'],
        ['Belum absen', $metrics['not_checked_in'], 'bg-slate-700'],
    ] as [$label, $value, $color])
        <div class="rounded-3xl border border-white bg-white p-5 shadow-sm">
            <div class="h-2 w-12 rounded-full {{ $color }}"></div>
            <p class="mt-5 text-sm text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-4xl font-black text-emerald-950">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <section class="rounded-3xl bg-white p-6 shadow-sm xl:col-span-2">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-emerald-950">Grafik Kehadiran Harian</h2>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Auto refresh ready</span>
        </div>
        <div id="dailyChart" class="mt-4"></div>
    </section>
    <section class="rounded-3xl bg-white p-6 shadow-sm">
        <h2 class="font-bold text-emerald-950">Persentase Bulanan</h2>
        <div id="monthlyChart" class="mt-4"></div>
    </section>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <section class="rounded-3xl bg-white p-6 shadow-sm">
        <h2 class="font-bold text-emerald-950">Keterlambatan Mingguan</h2>
        <div id="lateChart" class="mt-4"></div>
    </section>
    <section class="rounded-3xl bg-white p-6 shadow-sm">
        <h2 class="font-bold text-emerald-950">Izin/Sakit/Alfa</h2>
        <div class="mt-5 space-y-3">
            @foreach(['izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa'] as $key => $label)
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                    <span>{{ $label }}</span><strong>{{ $absenceStats[$key] ?? 0 }}</strong>
                </div>
            @endforeach
        </div>
    </section>
    <section class="rounded-3xl bg-white p-6 shadow-sm">
        <h2 class="font-bold text-emerald-950">Aktivitas Fingerprint</h2>
        <div class="mt-5 space-y-3">
            @forelse($fingerprintActivities as $activity)
                <div class="rounded-2xl border border-slate-100 px-4 py-3">
                    <div class="flex justify-between gap-3 text-sm">
                        <span class="font-semibold">{{ $activity->fingerprint?->name }}</span>
                        <span class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ $activity->action }} · {{ $activity->status }}</p>
                </div>
            @empty
                <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada aktivitas mesin.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
const green = '#047857', gold = '#d97706';
new ApexCharts(document.querySelector('#dailyChart'), {
    chart: {type: 'area', height: 310, toolbar: {show:false}},
    colors: [green], stroke: {curve:'smooth', width:3}, fill: {type:'gradient', gradient:{opacityFrom:.35, opacityTo:.05}},
    series: [{name: 'Hadir', data: @json($dailyChart['series'])}],
    xaxis: {categories: @json($dailyChart['labels'])}
}).render();
new ApexCharts(document.querySelector('#lateChart'), {
    chart: {type: 'bar', height: 260, toolbar: {show:false}},
    colors: [gold], plotOptions: {bar:{borderRadius:8}},
    series: [{name: 'Terlambat', data: @json($lateChart['series'])}],
    xaxis: {categories: @json($lateChart['labels'])}
}).render();
new ApexCharts(document.querySelector('#monthlyChart'), {
    chart: {type: 'radialBar', height: 280},
    colors: [green], series: [{{ $monthlyPercentage }}],
    labels: ['Kehadiran'], plotOptions: {radialBar:{hollow:{size:'65%'}, dataLabels:{value:{fontSize:'34px', fontWeight:800}}}}
}).render();
setTimeout(() => location.reload(), 300000);
</script>
@endpush
