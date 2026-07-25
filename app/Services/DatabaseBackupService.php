<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseBackupService
{
    public function create(string $type = 'manual', ?int $userId = null): Backup
    {
        $fileName = 'backup-'.now()->format('Ymd-His').'.sql';
        $relativePath = 'backups/'.$fileName;
        Storage::makeDirectory('backups');

        $this->dumpDatabase($relativePath);

        return Backup::create([
            'user_id' => $userId,
            'file_name' => $relativePath,
            'size' => Storage::size($relativePath),
            'type' => $type,
        ]);
    }

    private function dumpDatabase(string $relativePath): void
    {
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            File::copy(database_path('database.sqlite'), Storage::path($relativePath));

            return;
        }

        $database = config("database.connections.{$connection}");
        $mysqldump = $this->mysqldumpPath();

        if (! $mysqldump) {
            throw new RuntimeException('File mysqldump.exe tidak ditemukan. Pastikan Laragon/MySQL terpasang atau isi MYSQLDUMP_PATH di file .env.');
        }

        $command = [
            $mysqldump,
            '--host='.$database['host'],
            '--port='.$database['port'],
            '--user='.$database['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
        ];

        if (filled($database['password'] ?? null)) {
            $command[] = '--password='.$database['password'];
        }

        $command[] = $database['database'];

        $result = Process::timeout(120)->run($command);

        if (! $result->successful()) {
            Log::error('Database backup failed', [
                'exit_code' => $result->exitCode(),
                'error' => $result->errorOutput(),
            ]);

            throw new RuntimeException('Pastikan database aktif dan konfigurasi database benar.');
        }

        Storage::put($relativePath, $result->output());
    }

    private function mysqldumpPath(): ?string
    {
        $configuredPath = env('MYSQLDUMP_PATH');

        if ($configuredPath && File::exists($configuredPath)) {
            return $configuredPath;
        }

        $candidates = array_merge(
            glob('C:/laragon/bin/mysql/*/bin/mysqldump.exe') ?: [],
            glob('C:/xampp/mysql/bin/mysqldump.exe') ?: [],
            glob('C:/Program Files/MySQL/*/bin/mysqldump.exe') ?: [],
            ['mysqldump']
        );

        foreach ($candidates as $candidate) {
            if ($candidate === 'mysqldump' || File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
