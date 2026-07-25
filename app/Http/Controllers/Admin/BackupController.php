<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BackupController extends Controller
{
    public function index()
    {
        return view('backups.index', ['backups' => Backup::latest()->get()]);
    }

    public function store(DatabaseBackupService $backupService)
    {
        try {
            $backupService->create('manual', auth()->id());
        } catch (RuntimeException $exception) {
            return back()->with('error', 'Backup gagal. '.$exception->getMessage());
        }

        return back()->with('success', 'Backup database berhasil dibuat.');
    }

    public function download(Backup $backup)
    {
        return Storage::download($backup->file_name);
    }
}
