<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Absensi Madrasah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-emerald-950">
<main class="grid min-h-screen lg:grid-cols-2">
    <section class="hidden items-end bg-[radial-gradient(circle_at_20%_20%,#facc15_0,#facc1530_20%,transparent_40%),linear-gradient(135deg,#064e3b,#022c22)] p-12 text-white lg:flex">
        <div>
            <div class="mb-8 grid h-16 w-16 place-items-center rounded-3xl bg-amber-300 text-2xl font-black text-emerald-950">ن</div>
            <h1 class="max-w-xl text-5xl font-black leading-tight">Absensi fingerprint madrasah yang rapi, cepat, dan aman.</h1>
            <p class="mt-5 max-w-lg text-emerald-100">Pantau kehadiran siswa dan guru, sinkronisasi mesin LAN, dan ekspor laporan harian atau bulanan dari satu dashboard.</p>
        </div>
    </section>
    <section class="flex items-center justify-center p-6">
        <form method="post" action="{{ route('login.store') }}" class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
            @csrf
            <p class="text-sm font-semibold uppercase tracking-[.25em] text-emerald-700">Masuk Sistem</p>
            <h2 class="mt-2 text-3xl font-black text-emerald-950">Assalamu'alaikum</h2>
            <label class="mt-8 block text-sm font-semibold">Email</label>
            <input name="email" type="email" value="{{ old('email', 'admin@madrasah.test') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
            <label class="mt-4 block text-sm font-semibold">Password</label>
            <input name="password" type="password" value="password" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
            @error('email')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
            <button class="mt-6 w-full rounded-2xl bg-emerald-900 py-3 font-bold text-white shadow-lg shadow-emerald-900/20 hover:bg-emerald-800">Masuk</button>
            <p class="mt-5 text-center text-xs text-slate-500">Demo: admin@madrasah.test / password</p>
        </form>
    </section>
</main>
</body>
</html>
