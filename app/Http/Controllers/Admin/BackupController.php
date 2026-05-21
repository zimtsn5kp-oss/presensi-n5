<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        return view('backups.index', ['backups' => Backup::latest()->get()]);
    }

    public function store()
    {
        $fileName = 'backup-'.now()->format('Ymd-His').'.sql';
        $relativePath = 'backups/'.$fileName;
        Storage::makeDirectory('backups');

        if (config('database.default') === 'sqlite') {
            File::copy(database_path('database.sqlite'), Storage::path($relativePath));
        } else {
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s %s %s',
                escapeshellarg(config('database.connections.mysql.host')),
                escapeshellarg(config('database.connections.mysql.port')),
                escapeshellarg(config('database.connections.mysql.username')),
                config('database.connections.mysql.password') ? '--password='.escapeshellarg(config('database.connections.mysql.password')) : '',
                escapeshellarg(config('database.connections.mysql.database'))
            );

            $result = Process::timeout(120)->run($command);
            if (! $result->successful()) {
                return back()->with('error', 'Backup gagal. Pastikan mysqldump tersedia di PATH server.');
            }
            Storage::put($relativePath, $result->output());
        }

        Backup::create([
            'user_id' => auth()->id(),
            'file_name' => $relativePath,
            'size' => Storage::size($relativePath),
            'type' => 'manual',
        ]);

        return back()->with('success', 'Backup database berhasil dibuat.');
    }

    public function download(Backup $backup)
    {
        return Storage::download($backup->file_name);
    }
}
