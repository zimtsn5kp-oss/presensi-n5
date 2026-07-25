<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\Holiday;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);
        $teacherRole = Role::firstOrCreate(['name' => 'guru'], ['display_name' => 'Guru']);

        collect([
            ['students.manage', 'Kelola murid', 'master'],
            ['teachers.manage', 'Kelola guru', 'master'],
            ['attendance.absence', 'Input ketidakhadiran', 'attendance'],
            ['reports.view', 'Lihat laporan', 'reports'],
        ])->each(function ($row) use ($admin, $teacherRole) {
            $permission = Permission::firstOrCreate(['name' => $row[0]], ['display_name' => $row[1], 'group' => $row[2]]);
            $admin->permissions()->syncWithoutDetaching($permission->id);
            if (in_array($row[0], ['attendance.absence', 'reports.view'], true)) {
                $teacherRole->permissions()->syncWithoutDetaching($permission->id);
            }
        });

        User::factory()->create([
            'role_id' => $admin->id,
            'name' => 'Administrator Madrasah',
            'username' => 'admin',
            'email' => 'admin@madrasah.test',
            'password' => Hash::make('password'),
        ]);

        SchoolSetting::firstOrCreate([], [
            'school_name' => 'Madrasah Nurul Ilmi',
            'address' => 'Jl. Pendidikan Islam No. 7',
            'email' => 'info@madrasah.test',
            'phone' => '021-555-0101',
            'academic_year' => '2026/2027',
            'active_semester' => 'Ganjil',
        ]);

        $students = collect([
            ['nisn' => '0061234501', 'name' => 'Ahmad Fauzan', 'classroom' => '7A', 'gender' => 'L'],
            ['nisn' => '0061234502', 'name' => 'Aisyah Zahra', 'classroom' => '7A', 'gender' => 'P'],
            ['nisn' => '0061234503', 'name' => 'Muhammad Nabil', 'classroom' => '8B', 'gender' => 'L'],
            ['nisn' => '0061234504', 'name' => 'Siti Maryam', 'classroom' => '9A', 'gender' => 'P'],
        ])->map(fn ($data) => Student::firstOrCreate(['nisn' => $data['nisn']], $data));

        $teachers = collect([
            ['nip' => '198812122010011001', 'name' => 'Ust. Hasan Basri', 'subject' => 'Fiqih'],
            ['nip' => '199105052014012002', 'name' => 'Usth. Nur Aulia', 'subject' => 'Bahasa Arab'],
        ])->map(fn ($data) => Teacher::firstOrCreate(['nip' => $data['nip']], $data));

        $teachers->each(fn (Teacher $teacher) => app(\App\Services\TeacherAccountService::class)->sync($teacher));

        foreach (range(1, 5) as $day) {
            AttendanceSetting::firstOrCreate(['day_of_week' => $day, 'name' => 'Reguler'], [
                'check_in_start' => '06:00',
                'check_in_end' => '07:30',
                'late_after' => '07:00',
                'check_out_start' => '14:00',
                'check_out_end' => '16:00',
            ]);
        }

        Holiday::firstOrCreate(['name' => 'Tahun Baru Hijriyah 1 Muharram'], [
            'type' => 'hari_besar_islam',
            'start_date' => now()->startOfMonth()->addDays(10)->toDateString(),
            'end_date' => now()->startOfMonth()->addDays(10)->toDateString(),
        ]);

        $students->each(function (Student $student, int $index) {
            AttendanceLog::updateOrCreate([
                'attendable_type' => Student::class,
                'attendable_id' => $student->id,
                'attendance_date' => today()->toDateString(),
            ], [
                'check_in_at' => $index === 2 ? '07:18:00' : '06:48:00',
                'status' => $index === 2 ? 'terlambat' : 'hadir',
                'late_minutes' => $index === 2 ? 18 : 0,
                'source' => 'seeder',
            ]);
        });

    }
}
