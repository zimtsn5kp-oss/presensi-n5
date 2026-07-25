<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prisma</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-mts.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <style>
        [x-cloak]{display:none} .dt-input{border:1px solid #d1d5db;border-radius:.75rem;padding:.45rem .7rem}
        table.dataTable thead th{color:#065f46}
        #appSidebar{width:18rem;transition:width .2s ease,transform .2s ease}
        #appMain{transition:padding-left .2s ease}
        #sidebarOverlay{display:none}
        @media (max-width:1023px){
            #appSidebar{transform:translateX(-100%)}
            body.sidebar-open #appSidebar{transform:translateX(0)}
            body.sidebar-open #sidebarOverlay{display:block}
        }
        @media (min-width:1024px){
            #appMain{padding-left:18rem}
            body.sidebar-collapsed #appSidebar{width:5rem}
            body.sidebar-collapsed #appMain{padding-left:5rem}
            body.sidebar-collapsed .sidebar-label,
            body.sidebar-collapsed .sidebar-brand-text{display:none}
            body.sidebar-collapsed .sidebar-brand{justify-content:center;padding-left:.75rem;padding-right:.75rem}
            body.sidebar-collapsed .sidebar-logo{height:3rem;width:3rem;flex:none}
            body.sidebar-collapsed .sidebar-nav{padding-left:.5rem;padding-right:.5rem}
            body.sidebar-collapsed .sidebar-nav a{justify-content:center;padding-left:.75rem;padding-right:.75rem}
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
<div id="sidebarOverlay" class="fixed inset-0 z-30 bg-slate-950/50 lg:hidden"></div>
<div class="min-h-screen">
    <aside id="appSidebar" class="fixed inset-y-0 left-0 z-40 overflow-y-auto bg-emerald-950 text-white">
        <div class="sidebar-brand flex h-20 items-center gap-3 px-6">
            @if($schoolSetting?->logo_path)
                <div class="sidebar-logo grid h-14 w-14 place-items-center overflow-hidden rounded-2xl bg-white p-1 shadow">
                    <img src="{{ asset('storage/'.$schoolSetting->logo_path) }}" alt="Logo {{ $schoolSetting->school_name }}" class="h-full w-full object-contain">
                </div>
            @else
                <div class="sidebar-logo grid h-11 w-11 place-items-center rounded-2xl bg-amber-300 font-black text-emerald-950">A</div>
            @endif
            <div class="sidebar-brand-text">
                <p class="font-bold leading-tight">{{ $schoolSetting?->school_name ?? 'Absensi Madrasah' }}</p>
                <p class="text-xs text-emerald-100">Barcode Attendance System</p>
            </div>
        </div>
        <nav class="sidebar-nav space-y-1 px-4 pb-6">
            @php
                $items = [
                    ['Dashboard', 'dashboard', 'M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-5H4v5Zm10 0h6v-9h-6v9Z'],
                    ['Murid', 'students.index', 'M12 12c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4Zm-7 8c.8-3.1 3.6-5 7-5s6.2 1.9 7 5H5Z'],
                    ['Guru', 'teachers.index', 'M12 3 2 8l10 5 10-5-10-5Zm-6 9v4c2 2 10 2 12 0v-4l-6 3-6-3Z'],
                    ['Absensi', 'attendance.barcode', 'M4 5h4V3H2v6h2V5Zm12-2v2h4v4h2V3h-6Zm4 16h-4v2h6v-6h-2v4ZM4 15H2v6h6v-2H4v-4Zm3-6h2v2H7V9Zm4 0h6v2h-6V9Zm-4 4h10v2H7v-2Zm0 4h6v2H7v-2Zm8 0h2v2h-2v-2Z'],
                    ['Ketidakhadiran', 'attendance.absence', 'M12 2 3 6v6c0 5 4 8 9 10 5-2 9-5 9-10V6l-9-4Zm-1 6h2v4h4v2h-6V8Z'],
                    ['Hari Libur', 'holidays.index', 'M7 2h2v2h6V2h2v2h3v18H4V4h3V2Zm13 8H4v10h16V10Z'],
                    ['Rekap Absensi', 'recaps.monthly', 'M4 3h16v18H4V3Zm2 5h12V5H6v3Zm0 3v3h3v-3H6Zm5 0v3h3v-3h-3Zm5 0v3h2v-3h-2ZM6 16v3h3v-3H6Zm5 0v3h3v-3h-3Zm5 0v3h2v-3h-2Z'],
                    ['Backup', 'backups.index', 'M12 3a9 9 0 0 0-9 9h3l-4 4-4-4h3a11 11 0 1 1 3.2 7.8l1.4-1.4A9 9 0 1 0 12 3Zm-1 5h2v5h4v2h-6V8Z'],
                    ['Pengaturan', 'settings.index', 'M19.4 13.5a7.8 7.8 0 0 0 0-3l2-1.5-2-3.5-2.4 1a8 8 0 0 0-2.6-1.5L14 2h-4l-.4 3a8 8 0 0 0-2.6 1.5l-2.4-1-2 3.5 2 1.5a7.8 7.8 0 0 0 0 3l-2 1.5 2 3.5 2.4-1a8 8 0 0 0 2.6 1.5l.4 3h4l.4-3a8 8 0 0 0 2.6-1.5l2.4 1 2-3.5-2-1.5ZM12 15a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z'],
                ];
            @endphp
            @php($adminOnlyRoutes = ['teachers.index', 'holidays.index', 'backups.index', 'settings.index'])
            @foreach($items as [$label, $route, $path])
                @continue(in_array($route, $adminOnlyRoutes, true) && ! auth()->user()->hasRole('administrator'))
                <a href="{{ Route::has($route) ? route($route) : '#' }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm transition {{ request()->routeIs($route) ? 'bg-white text-emerald-950 shadow' : 'text-emerald-50 hover:bg-white/10' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $path }}"/></svg>
                    <span class="sidebar-label">{{ $label }}</span>
                </a>
            @endforeach
        </nav>
    </aside>
    <main id="appMain" class="min-w-0">
        <header class="sticky top-0 z-20 border-b border-emerald-100 bg-white/90 px-5 py-4 backdrop-blur">
            <div class="flex items-center justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <button id="sidebarToggle" type="button" title="Buka atau tutup sidebar" aria-label="Buka atau tutup sidebar" aria-expanded="true" class="grid h-11 w-11 flex-none place-items-center rounded-xl border border-slate-200 text-emerald-950 hover:bg-emerald-50">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-[.2em] text-emerald-700">Madrasah SaaS</p>
                        <h1 class="truncate text-xl font-bold text-emerald-950">{{ $title ?? 'Dashboard' }}</h1>
                    </div>
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
<script>
const sidebarBody = document.body;
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const desktopSidebar = window.matchMedia('(min-width: 1024px)');

function updateSidebarState() {
    const expanded = desktopSidebar.matches
        ? !sidebarBody.classList.contains('sidebar-collapsed')
        : sidebarBody.classList.contains('sidebar-open');
    sidebarToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
}

if (desktopSidebar.matches && localStorage.getItem('prisma-sidebar-collapsed') === '1') {
    sidebarBody.classList.add('sidebar-collapsed');
}
updateSidebarState();

sidebarToggle.addEventListener('click', function () {
    if (desktopSidebar.matches) {
        sidebarBody.classList.toggle('sidebar-collapsed');
        localStorage.setItem('prisma-sidebar-collapsed', sidebarBody.classList.contains('sidebar-collapsed') ? '1' : '0');
    } else {
        sidebarBody.classList.toggle('sidebar-open');
    }
    updateSidebarState();
});

sidebarOverlay.addEventListener('click', function () {
    sidebarBody.classList.remove('sidebar-open');
    updateSidebarState();
});

document.querySelectorAll('#appSidebar a').forEach(function (link) {
    link.addEventListener('click', function () {
        if (!desktopSidebar.matches) {
            sidebarBody.classList.remove('sidebar-open');
        }
    });
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        sidebarBody.classList.remove('sidebar-open');
        updateSidebarState();
    }
});

desktopSidebar.addEventListener('change', function () {
    sidebarBody.classList.remove('sidebar-open');
    updateSidebarState();
});
</script>
@stack('scripts')
</body>
</html>
