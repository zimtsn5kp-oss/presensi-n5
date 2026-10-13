<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prisma</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-mts.png') }}">
    @vite('resources/css/app.css')
    <style>
        body{font-family:ui-sans-serif,system-ui,sans-serif}
        .login-bg{
            background:
                linear-gradient(135deg, rgba(4,120,87,.18) 0 25%, transparent 25% 50%, rgba(245,158,11,.10) 50% 75%, transparent 75%) 0 0 / 28px 28px,
                radial-gradient(circle at 20% 15%, rgba(16,185,129,.22), transparent 34rem),
                radial-gradient(circle at 82% 80%, rgba(250,204,21,.16), transparent 30rem),
                #022c22;
        }
    </style>
</head>
<body class="login-bg min-h-screen">
<main class="flex min-h-screen items-center justify-center p-4 sm:p-6">
    <section class="w-full max-w-sm overflow-hidden rounded-[1.5rem] border border-white/30 bg-white shadow-2xl shadow-emerald-950/40">
        <div class="border-b border-emerald-100 bg-emerald-50/70 px-5 py-4 text-center sm:px-6 sm:py-5">
            <img src="{{ asset('images/logo-prisma-transparent.png') }}" alt="Logo Prisma" class="mx-auto h-32 w-32 object-contain sm:h-36 sm:w-36">
        </div>

        <form method="post" action="{{ route('login.store') }}" class="p-5 sm:p-6">
            @csrf
            <label class="block text-sm font-semibold text-emerald-950">Username</label>
            <div class="mt-2 flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 focus-within:border-emerald-500 focus-within:bg-white">
                <svg class="h-5 w-5 flex-none text-emerald-700" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4Zm-7 8c.8-3.1 3.6-5 7-5s6.2 1.9 7 5H5Z"/></svg>
                <input name="username" value="{{ old('username') }}" autocomplete="username" class="w-full bg-transparent py-2.5 outline-none" autofocus>
            </div>

            <label class="mt-5 block text-sm font-semibold text-emerald-950">Password</label>
            <div class="mt-2 flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 focus-within:border-emerald-500 focus-within:bg-white">
                <svg class="h-5 w-5 flex-none text-emerald-700" viewBox="0 0 24 24" fill="currentColor"><path d="M17 9V7A5 5 0 0 0 7 7v2H5v12h14V9h-2Zm-8 0V7a3 3 0 0 1 6 0v2H9Zm4 4v4h-2v-4h2Z"/></svg>
                <input name="password" type="password" autocomplete="current-password" class="w-full bg-transparent py-2.5 outline-none">
            </div>

            @error('username')<p class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            <button class="mt-5 w-full rounded-2xl bg-emerald-900 py-2.5 font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-800">Masuk</button>
            <p class="mt-4 text-center text-xs font-medium text-slate-400">MTs Negeri 5 Kulon Progo</p>
        </form>
    </section>
</main>
</body>
</html>
