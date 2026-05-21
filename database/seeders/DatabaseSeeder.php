<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\Fingerprint;
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
            ['students.manage', 'Kelola siswa', 'master'],
            ['teachers.manage', 'Kelola guru', 'master'],
            ['fingerprints.manage', 'Kelola fingerprint', 'fingerprint'],
            ['attendance.manual', 'Input kehadiran manual', 'attendance'],
            ['attendance.absence', 'Input ketidakhadiran', 'attendance'],
            ['reports.view', 'Lihat laporan', 'reports'],
        ])->each(function ($row) use ($admin, $teacherRole) {
            $permission = Permission::firstOrCreate(['name' => $row[0]], ['display_name' => $row[1], 'group' => $row[2]]);
            $admin->permissions()->syncWithoutDetaching($permission->id);
            if (in_array($row[0], ['attendance.manual', 'attendance.absence', 'reports.view'], true)) {
                $teacherRole->permissions()->syncWithoutDetaching($permission->id);
            }
        });

        User::factory()->create([
            'role_id' => $admin->id,
            'name' => 'Administrator Madrasah',
            'email' => 'admin@madrasah.test',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'role_id' => $teacherRole->id,
            'name' => 'Guru Piket',
            'email' => 'guru@madrasah.test',
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
            ['nisn' => '0061234501', 'name' => 'Ahmad Fauzan', 'classroom' => 'VII A', 'gender' => 'L', 'fingerprint_id' => 1001],
            ['nisn' => '0061234502', 'name' => 'Aisyah Zahra', 'classroom' => 'VII A', 'gender' => 'P', 'fingerprint_id' => 1002],
            ['nisn' => '0061234503', 'name' => 'Muhammad Nabil', 'classroom' => 'VIII B', 'gender' => 'L', 'fingerprint_id' => 1003],
            ['nisn' => '0061234504', 'name' => 'Siti Maryam', 'classroom' => 'IX A', 'gender' => 'P', 'fingerprint_id' => 1004],
        ])->map(fn ($data) => Student::firstOrCreate(['nisn' => $data['nisn']], $data + ['address' => 'Komplek madrasah']));

        $teachers = collect([
            ['nip' => '198812122010011001', 'name' => 'Ust. Hasan Basri', 'subject' => 'Fiqih', 'position' => 'Wali Kelas', 'fingerprint_id' => 2001],
            ['nip' => '199105052014012002', 'name' => 'Usth. Nur Aulia', 'subject' => 'Bahasa Arab', 'position' => 'Guru Mapel', 'fingerprint_id' => 2002],
        ])->map(fn ($data) => Teacher::firstOrCreate(['nip' => $data['nip']], $data));

        Fingerprint::firstOrCreate(['ip_address' => '192.168.1.201'], ['name' => 'Mesin Gerbang Utama', 'port' => 4370]);

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
                'machine_user_id' => $student->fingerprint_id,
                'check_in_at' => $index === 2 ? '07:18:00' : '06:48:00',
                'status' => $index === 2 ? 'terlambat' : 'hadir',
                'late_minutes' => $index === 2 ? 18 : 0,
                'source' => 'seeder',
            ]);
        });

        $teachers->each(function (Teacher $teacher) {
            AttendanceLog::updateOrCreate([
                'attendable_type' => Teacher::class,
                'attendable_id' => $teacher->id,
                'attendance_date' => today()->toDateString(),
            ], [
                'machine_user_id' => $teacher->fingerprint_id,
                'check_in_at' => '06:35:00',
                'status' => 'hadir',
                'source' => 'seeder',
            ]);
        });
    }
}
