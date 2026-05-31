<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prisma</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-mts.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-emerald-950">
<main class="flex min-h-screen items-center justify-center p-6">
    <form method="post" action="{{ route('login.store') }}" class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
        @csrf
        <div class="text-center">
            <img src="{{ asset('images/logo-mts.png') }}" alt="Logo MTs Negeri 5 Kulon Progo" class="mx-auto h-28 w-28 object-contain">
            <p class="mt-4 text-sm font-bold uppercase tracking-[.25em] text-emerald-700">Prisma</p>
            <h1 class="mt-2 text-2xl font-black text-emerald-950">Presensi Siswa MTs Negeri 5 Kulon Progo</h1>
        </div>
        <label class="mt-8 block text-sm font-semibold">Username</label>
        <input name="username" value="{{ old('username') }}" autocomplete="username" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
        <label class="mt-4 block text-sm font-semibold">Password</label>
        <input name="password" type="password" autocomplete="current-password" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-emerald-500 focus:outline-none">
        @error('username')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
        <button class="mt-6 w-full rounded-2xl bg-emerald-900 py-3 font-bold text-white shadow-lg shadow-emerald-900/20 hover:bg-emerald-800">Masuk</button>
    </form>
</main>
</body>
</html>
