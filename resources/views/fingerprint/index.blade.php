@extends('layouts.app', ['title' => 'Mesin Fingerprint'])
@section('content')
<div class="grid gap-6 xl:grid-cols-3">
    <form method="post" action="{{ route('fingerprints.store') }}" class="rounded-3xl bg-white p-6 shadow-sm">
        @csrf
        <h2 class="font-bold text-emerald-950">Tambah Mesin</h2>
        <label class="mt-4 block text-sm font-semibold">Nama<input name="name" class="mt-2 w-full rounded-2xl border px-4 py-3" placeholder="Mesin Gerbang Utama"></label>
        <label class="mt-4 block text-sm font-semibold">IP Address<input name="ip_address" class="mt-2 w-full rounded-2xl border px-4 py-3" placeholder="192.168.1.201"></label>
        <label class="mt-4 block text-sm font-semibold">Port<input name="port" value="4370" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
        <label class="mt-4 block text-sm font-semibold">Serial Number<input name="serial_number" class="mt-2 w-full rounded-2xl border px-4 py-3"></label>
        <button class="mt-5 rounded-xl bg-emerald-900 px-5 py-3 font-semibold text-white">Simpan Mesin</button>
    </form>
    <section class="xl:col-span-2 rounded-3xl bg-white p-6 shadow-sm">
        <h2 class="font-bold text-emerald-950">Status Koneksi</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @forelse($machines as $machine)
                <article class="rounded-3xl border border-slate-100 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div><h3 class="font-bold">{{ $machine->name }}</h3><p class="text-sm text-slate-500">{{ $machine->ip_address }}:{{ $machine->port }}</p></div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $machine->is_online ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $machine->is_online ? 'Online' : 'Offline' }}</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl bg-slate-50 p-3"><p class="text-slate-500">Last sync</p><strong>{{ $machine->last_sync_at?->diffForHumans() ?? '-' }}</strong></div>
                        <div class="rounded-2xl bg-slate-50 p-3"><p class="text-slate-500">User mesin</p><strong>{{ $machine->machine_user_count }}</strong></div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach(['test'=>'Tes','download'=>'Download Log','sync-users'=>'Sync User','backup-templates'=>'Backup FP','restore-templates'=>'Restore FP'] as $action => $label)
                            <form method="post" action="{{ route('fingerprints.action',$machine) }}">@csrf<input type="hidden" name="action" value="{{ $action }}"><button class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">{{ $label }}</button></form>
                        @endforeach
                    </div>
                </article>
            @empty
                <p class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">Belum ada mesin fingerprint.</p>
            @endforelse
        </div>
    </section>
</div>
<section class="mt-6 rounded-3xl bg-white p-6 shadow-sm">
    <h2 class="font-bold text-emerald-950">Riwayat Sinkronisasi</h2>
    <div class="mt-4 overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="text-emerald-800"><th class="py-3">Waktu</th><th>Mesin</th><th>Aksi</th><th>Status</th><th>Pesan</th></tr></thead><tbody>
        @foreach($histories as $history)<tr class="border-t"><td class="py-3">{{ $history->created_at->format('d/m/Y H:i') }}</td><td>{{ $history->fingerprint?->name }}</td><td>{{ $history->action }}</td><td>{{ $history->status }}</td><td>{{ $history->message }}</td></tr>@endforeach
    </tbody></table></div>
</section>
@endsection
