<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Absensi Madrasah' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <style>
        [x-cloak]{display:none} .dt-input{border:1px solid #d1d5db;border-radius:.75rem;padding:.45rem .7rem}
        table.dataTable thead th{color:#065f46}
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
<div class="min-h-screen lg:flex">
    <aside class="bg-emerald-950 text-white lg:fixed lg:inset-y-0 lg:w-72">
        <div class="flex h-20 items-center gap-3 px-6">
            @if($schoolSetting?->logo_path)
                <div class="grid h-14 w-14 place-items-center overflow-hidden rounded-2xl bg-white p-1 shadow">
                    <img src="{{ asset('storage/'.$schoolSetting->logo_path) }}" alt="Logo {{ $schoolSetting->school_name }}" class="h-full w-full object-contain">
                </div>
            @else
                <div class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-300 text-emerald-950 font-black">A</div>
            @endif
            <div>
                <p class="font-bold leading-tight">{{ $schoolSetting?->school_name ?? 'Absensi Madrasah' }}</p>
                <p class="text-xs text-emerald-100">Fingerprint LAN System</p>
            </div>
        </div>
        <nav class="space-y-1 px-4 pb-6">
            @php
                $items = [
                    ['Dashboard', 'dashboard', 'M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-5H4v5Zm10 0h6v-9h-6v9Z'],
                    ['Siswa', 'students.index', 'M12 12c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4Zm-7 8c.8-3.1 3.6-5 7-5s6.2 1.9 7 5H5Z'],
                    ['Guru', 'teachers.index', 'M12 3 2 8l10 5 10-5-10-5Zm-6 9v4c2 2 10 2 12 0v-4l-6 3-6-3Z'],
                    ['Fingerprint', 'fingerprints.index', 'M12 2a6 6 0 0 0-6 6v4a6 6 0 0 0 12 0V8a6 6 0 0 0-6-6Zm0 3a3 3 0 0 1 3 3v4a3 3 0 0 1-6 0V8a3 3 0 0 1 3-3Z'],
                    ['Manual', 'attendance.manual', 'M5 4h14v2H5V4Zm0 4h14v12H5V8Zm3 3v2h8v-2H8Z'],
                    ['Izin/Sakit', 'attendance.absence', 'M12 2 3 6v6c0 5 4 8 9 10 5-2 9-5 9-10V6l-9-4Zm-1 6h2v4h4v2h-6V8Z'],
                    ['Hari Libur', 'holidays.index', 'M7 2h2v2h6V2h2v2h3v18H4V4h3V2Zm13 8H4v10h16V10Z'],
                    ['Laporan', 'reports.index', 'M5 3h14v18H5V3Zm3 4v2h8V7H8Zm0 4v2h8v-2H8Zm0 4v2h5v-2H8Z'],
                    ['Backup', 'backups.index', 'M12 3a9 9 0 0 0-9 9h3l-4 4-4-4h3a11 11 0 1 1 3.2 7.8l1.4-1.4A9 9 0 1 0 12 3Zm-1 5h2v5h4v2h-6V8Z'],
                    ['Pengaturan', 'settings.index', 'M19.4 13.5a7.8 7.8 0 0 0 0-3l2-1.5-2-3.5-2.4 1a8 8 0 0 0-2.6-1.5L14 2h-4l-.4 3a8 8 0 0 0-2.6 1.5l-2.4-1-2 3.5 2 1.5a7.8 7.8 0 0 0 0 3l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 2.6 1.5l.4 3h4l.4-3a8 8 0 0 0 2.6-1.5l2.4 1 2-3.5-2-1.5ZM12 15a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z'],
                ];
            @endphp
            @foreach($items as [$label, $route, $path])
                <a href="{{ Route::has($route) ? route($route) : '#' }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm transition {{ request()->routeIs($route) ? 'bg-white text-emerald-950 shadow' : 'text-emerald-50 hover:bg-white/10' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $path }}"/></svg>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
    </aside>
    <main class="min-w-0 flex-1 lg:pl-72">
        <header class="sticky top-0 z-20 border-b border-emerald-100 bg-white/90 px-5 py-4 backdrop-blur">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[.2em] text-emerald-700">Madrasah SaaS</p>
                    <h1 class="text-xl font-bold text-emerald-950">{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <form method="post" action="{{ route('logout') }}">@csrf
                    <button class="rounded-xl bg-emerald-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800">Keluar</button>
                </form>
            </div>
        </header>
        <section class="p-5 lg:p-8">
            @if(session('success') || session('error'))
                <div class="mb-5 rounded-2xl border {{ session('error') ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }} px-4 py-3 text-sm shadow-sm">
                    {{ session('success') ?? session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </section>
    </main>
</div>
@stack('scripts')
</body>
</html>
