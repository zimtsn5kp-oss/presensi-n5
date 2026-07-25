@extends('layouts.app', ['title' => 'Backup Database'])
@section('content')
<section class="rounded-2xl bg-white p-4 shadow-sm sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><h2 class="font-bold text-emerald-950">Riwayat Backup</h2></div>
        <form method="post" action="{{ route('backups.store') }}" class="w-full sm:w-auto">@csrf<button class="w-full rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white sm:w-auto">Backup Manual</button></form>
    </div>
    <div class="mt-6 overflow-x-auto"><table class="min-w-[720px] w-full text-left text-sm"><thead><tr class="text-emerald-800"><th class="py-3">Waktu</th><th>File</th><th>Ukuran</th><th>Tipe</th><th>Aksi</th></tr></thead><tbody>
        @forelse($backups as $backup)<tr class="border-t"><td class="py-3">{{ $backup->created_at->format('d/m/Y H:i') }}</td><td>{{ $backup->file_name }}</td><td>{{ number_format($backup->size / 1024, 1) }} KB</td><td>{{ $backup->type }}</td><td><a class="font-semibold text-emerald-700" href="{{ route('backups.download',$backup) }}">Download</a></td></tr>@empty
        <tr><td colspan="5" class="py-10 text-center text-slate-500">Belum ada backup.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
