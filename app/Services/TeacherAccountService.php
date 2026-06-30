<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;

class TeacherAccountService
{
    public function sync(Teacher $teacher, ?string $password = null): User
    {
        $account = User::firstOrNew(['teacher_id' => $teacher->id]);
        $account->fill([
            'role_id' => Role::where('name', 'guru')->value('id'),
            'name' => $teacher->name,
            'username' => $teacher->nip,
            'email' => $account->email ?: $teacher->nip.'@prisma.local',
            'is_active' => (bool) ($teacher->is_active ?? true),
        ]);

        if (! $account->exists || filled($password)) {
            $account->password = $password ?: 'prisma123';
        }

        $account->save();

        return $account;
    }

    public function deactivate(Teacher $teacher): void
    {
        User::where('teacher_id', $teacher->id)->update(['is_active' => false]);
    }
}
