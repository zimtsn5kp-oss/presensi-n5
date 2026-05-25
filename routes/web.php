<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FingerprintController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:administrator')->group(function () {
        Route::get('students/data', [StudentController::class, 'data'])->name('students.data');
        Route::get('students/export/excel', [StudentController::class, 'exportExcel'])->name('students.export.excel');
        Route::get('students/export/pdf', [StudentController::class, 'exportPdf'])->name('students.export.pdf');
        Route::post('students/sync-to-machine', [StudentController::class, 'syncToMachine'])->name('students.sync-to-machine');
        Route::resource('students', StudentController::class)->except('show');

        Route::get('teachers/data', [TeacherController::class, 'data'])->name('teachers.data');
        Route::get('teachers/export/excel', [TeacherController::class, 'exportExcel'])->name('teachers.export.excel');
        Route::get('teachers/export/pdf', [TeacherController::class, 'exportPdf'])->name('teachers.export.pdf');
        Route::resource('teachers', TeacherController::class)->except('show');

        Route::resource('fingerprints', FingerprintController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('fingerprints/{fingerprint}/action', [FingerprintController::class, 'action'])->name('fingerprints.action');

        Route::resource('holidays', HolidayController::class)->only(['index', 'store', 'destroy']);
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings/school', [SettingController::class, 'updateSchool'])->name('settings.school');
        Route::post('settings/attendance', [SettingController::class, 'storeAttendance'])->name('settings.attendance');
    });

    Route::middleware('role:administrator,guru')->group(function () {
        Route::get('attendance/barcode', [AttendanceController::class, 'barcodeForm'])->name('attendance.barcode');
        Route::post('attendance/barcode', [AttendanceController::class, 'storeBarcode'])->name('attendance.barcode.store');
        Route::get('attendance/manual', [AttendanceController::class, 'manualForm'])->name('attendance.manual');
        Route::post('attendance/manual', [AttendanceController::class, 'storeManual'])->name('attendance.manual.store');
        Route::get('attendance/absence', [AttendanceController::class, 'absenceForm'])->name('attendance.absence');
        Route::post('attendance/absence', [AttendanceController::class, 'storeAbsence'])->name('attendance.absence.store');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/excel', [ReportController::class, 'excel'])->name('reports.excel');
        Route::get('reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    });
});
