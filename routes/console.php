<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('backup:database', function (\App\Services\DatabaseBackupService $backupService) {
    $backup = $backupService->create('automatic');

    $this->info('Backup database berhasil dibuat: '.$backup->file_name);
})->purpose('Create an automatic database backup');

Schedule::command('backup:database')->monthlyOn(1, '23:00');
