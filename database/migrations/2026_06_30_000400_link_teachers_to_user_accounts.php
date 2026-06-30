<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->unique()->after('role_id')->constrained('teachers')->nullOnDelete();
        });

        $teacherRoleId = DB::table('roles')->where('name', 'guru')->value('id');
        $genericGuru = DB::table('users')->where('role_id', $teacherRoleId)->where('username', 'guru')->first();

        foreach (DB::table('teachers')->whereNull('deleted_at')->orderBy('id')->get() as $index => $teacher) {
            if (blank($teacher->nip)) {
                continue;
            }

            $account = $index === 0 ? $genericGuru : null;

            if ($account) {
                DB::table('users')->where('id', $account->id)->update([
                    'teacher_id' => $teacher->id,
                    'name' => $teacher->name,
                    'username' => $teacher->nip,
                    'password' => Hash::make('prisma123'),
                    'is_active' => $teacher->is_active,
                    'updated_at' => now(),
                ]);
                continue;
            }

            DB::table('users')->insert([
                'role_id' => $teacherRoleId,
                'teacher_id' => $teacher->id,
                'name' => $teacher->name,
                'username' => $teacher->nip,
                'email' => $teacher->nip.'@prisma.local',
                'password' => Hash::make('prisma123'),
                'is_active' => $teacher->is_active,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
        });
    }
};
