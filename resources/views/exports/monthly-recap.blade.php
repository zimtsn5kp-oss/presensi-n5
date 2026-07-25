<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:Arial,sans-serif;font-size:11px;color:#111827}
        h2{margin:0 0 6px;font-size:18px}
        p{margin:0 0 12px;color:#475569}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #cbd5e1;padding:5px;text-align:center}
        th{background:#f1f5f9;font-weight:bold}
        .left{text-align:left}
        .name{font-weight:bold}
        .holiday{background:#fff7ed;color:#9a3412}
        .weekend{background:#fef2f2;color:#b91c1c}
        .present{color:#047857;font-weight:bold}
        .late{color:#b45309;font-weight:bold}
        .permit{color:#1d4ed8;font-weight:bold}
        .sick{color:#c2410c;font-weight:bold}
        .alpha{color:#b91c1c;font-weight:bold}
    </style>
</head>
<body>
    <h2>Rekap Absensi: {{ $title }} - {{ $filters['classroom'] ?: 'Semua Kelas' }}</h2>
    <p>H = Hadir, T = Terlambat, I = Izin, S = Sakit, A = Alfa</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">NISN</th>
                <th rowspan="2" class="left">Nama Murid</th>
                <th rowspan="2">Kelas</th>
                <th colspan="{{ $dates->count() }}">Tanggal</th>
                <th colspan="4">Hasil</th>
            </tr>
            <tr>
                @foreach($dates as $date)
                    <th class="{{ $date['is_weekend'] ? 'weekend' : ($date['is_holiday'] ? 'holiday' : '') }}">{{ $date['date']->day }}</th>
                @endforeach
                <th>H</th>
                <th>S</th>
                <th>I</th>
                <th>A</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['student']->nisn }}</td>
                    <td class="left name">{{ $row['student']->name }}</td>
                    <td>{{ $row['student']->classroom }}</td>
                    @foreach($dates as $date)
                        @php($status = $row['days'][$date['key']])
                        <td class="{{ $date['is_weekend'] ? 'weekend' : ($date['is_holiday'] ? 'holiday' : '') }}">
                            @if($status === 'hadir')
                                <span class="present">H</span>
                            @elseif($status === 'terlambat')
                                <span class="late">T</span>
                            @elseif($status === 'izin')
                                <span class="permit">I</span>
                            @elseif($status === 'sakit')
                                <span class="sick">S</span>
                            @elseif($status === 'alfa')
                                <span class="alpha">A</span>
                            @elseif($date['is_holiday'])
                                -
                            @endif
                        </td>
                    @endforeach
                    <td>{{ $row['summary']['hadir'] }}</td>
                    <td>{{ $row['summary']['sakit'] }}</td>
                    <td>{{ $row['summary']['izin'] }}</td>
                    <td>{{ $row['summary']['alfa'] }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ $dates->count() + 8 }}">Tidak ada murid pada kelas yang dipilih.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
