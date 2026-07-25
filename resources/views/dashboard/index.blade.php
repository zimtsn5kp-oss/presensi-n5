@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
    @foreach([
        ['Total Murid', $metrics['total_students'], 'bg-teal-600', null],
        ['Murid Hadir', $metrics['student_present'], 'bg-emerald-600', null],
        ['Terlambat', $metrics['late'], 'bg-amber-500', 'late'],
        ['Tidak Hadir', $metrics['absent'], 'bg-red-500', 'absent'],
        ['Belum Absen', $metrics['not_checked_in'], 'bg-slate-700', 'not_checked_in'],
    ] as [$label, $value, $color, $detail])
        @if($detail)
            <a href="{{ route('dashboard.details', $detail) }}" target="_blank" rel="noopener" class="rounded-3xl border border-white bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="h-2 w-12 rounded-full {{ $color }}"></div>
                <div class="mt-5 flex items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">{{ $label }}</p>
                    <span class="text-lg text-slate-400">&rsaquo;</span>
                </div>
                <p class="mt-2 text-4xl font-black text-emerald-950">{{ $value }}</p>
            </a>
        @else
            <div class="rounded-3xl border border-white bg-white p-5 shadow-sm">
                <div class="h-2 w-12 rounded-full {{ $color }}"></div>
                <p class="mt-5 text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-4xl font-black text-emerald-950">{{ $value }}</p>
            </div>
        @endif
    @endforeach
</div>

<section class="mt-6 rounded-3xl bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="font-bold text-emerald-950">Grafik Kehadiran, Sakit, Izin, dan Alfa</h2>
            <p class="text-sm text-slate-500">{{ $statusChart['title'] }}</p>
        </div>
        <form class="flex w-full items-end gap-3 sm:w-auto">
            <label class="min-w-48 flex-1 text-sm font-semibold text-emerald-950">Filter Kelas
                <select name="classroom" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom }}" @selected($selectedClassroom===$classroom)>{{ $classroom }}</option>
                    @endforeach
                </select>
            </label>
            <button class="rounded-2xl bg-emerald-900 px-5 py-3 font-semibold text-white">Tampilkan</button>
        </form>
    </div>
    <div id="statusChart" class="mt-5"></div>
</section>
@endsection

@push('scripts')
<script>
new ApexCharts(document.querySelector('#statusChart'), {
    chart: {type: 'bar', height: 390, stacked: true, toolbar: {show: false}},
    colors: ['#059669', '#f59e0b', '#2563eb', '#dc2626'],
    series: @json($statusChart['series']),
    plotOptions: {bar: {borderRadius: 3, columnWidth: '72%'}},
    dataLabels: {enabled: false},
    stroke: {show: true, width: 1, colors: ['#ffffff']},
    xaxis: {categories: @json($statusChart['labels']), title: {text: 'Tanggal'}},
    yaxis: {min: 0, forceNiceScale: true, title: {text: 'Jumlah Murid'}},
    legend: {position: 'top', horizontalAlign: 'left'},
    grid: {borderColor: '#e2e8f0'},
    tooltip: {shared: true, intersect: false}
}).render();
setTimeout(() => location.reload(), 300000);
</script>
@endpush
