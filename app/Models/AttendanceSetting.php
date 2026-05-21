<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = ['day_of_week', 'name', 'check_in_start', 'check_in_end', 'late_after', 'check_out_start', 'check_out_end', 'is_ramadhan', 'is_active'];

    protected function casts(): array
    {
        return ['is_ramadhan' => 'boolean', 'is_active' => 'boolean'];
    }
}
