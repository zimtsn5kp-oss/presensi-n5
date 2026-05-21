<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = ['school_name', 'logo_path', 'address', 'email', 'phone', 'academic_year', 'active_semester', 'meta'];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }
}
