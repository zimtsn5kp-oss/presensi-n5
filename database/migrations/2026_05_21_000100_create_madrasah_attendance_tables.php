<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('Madrasah Nurul Ilmi');
            $table->string('logo_path')->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('active_semester')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nisn')->unique();
            $table->string('name');
            $table->string('classroom')->index();
            $table->enum('gender', ['L', 'P']);
            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('fingerprint_id')->nullable()->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->nullable()->unique();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->string('position')->nullable();
            $table->unsignedInteger('fingerprint_id')->nullable()->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fingerprints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address');
            $table->unsignedSmallInteger('port')->default(4370);
            $table->string('serial_number')->nullable();
            $table->boolean('is_online')->default(false)->index();
            $table->timestamp('last_sync_at')->nullable();
            $table->unsignedInteger('machine_user_count')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('fingerprint_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fingerprint_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('owner');
            $table->unsignedInteger('machine_user_id')->index();
            $table->unsignedTinyInteger('finger_index')->default(0);
            $table->longText('template_data');
            $table->string('checksum')->nullable();
            $table->timestamp('backed_up_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->nullable()->index();
            $table->string('name')->default('Reguler');
            $table->time('check_in_start');
            $table->time('check_in_end');
            $table->time('late_after');
            $table->time('check_out_start')->nullable();
            $table->time('check_out_end')->nullable();
            $table->boolean('is_ramadhan')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fingerprint_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('attendable');
            $table->unsignedInteger('machine_user_id')->nullable()->index();
            $table->date('attendance_date')->index();
            $table->time('check_in_at')->nullable();
            $table->time('check_out_at')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alfa', 'dispensasi'])->default('hadir')->index();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->string('source')->default('fingerprint');
            $table->text('note')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->unique(['attendable_type', 'attendable_id', 'attendance_date'], 'attendance_unique_daily');
        });

        Schema::create('manual_attendances', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('attendable');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('attendance_date')->index();
            $table->string('task_type');
            $table->text('description')->nullable();
            $table->string('letter_path')->nullable();
            $table->timestamps();
        });

        Schema::create('absence_permissions', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('attendable');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('absence_date')->index();
            $table->enum('type', ['izin', 'sakit', 'alfa', 'dispensasi'])->index();
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->string('name');
            $table->enum('type', ['nasional', 'madrasah', 'cuti_bersama', 'akademik', 'hari_besar_islam'])->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('fingerprint_sync_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fingerprint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->enum('status', ['success', 'failed', 'running'])->default('running')->index();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_name');
            $table->string('disk')->default('local');
            $table->unsignedBigInteger('size')->default(0);
            $table->enum('type', ['manual', 'automatic'])->default('manual');
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'activity_logs',
            'backups',
            'fingerprint_sync_histories',
            'holidays',
            'absence_permissions',
            'manual_attendances',
            'attendance_logs',
            'attendance_settings',
            'fingerprint_templates',
            'fingerprints',
            'teachers',
            'students',
            'school_settings',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
