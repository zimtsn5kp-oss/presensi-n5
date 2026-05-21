<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualAttendance extends Model
{
    protected $fillable = ['attendable_type', 'attendable_id', 'created_by', 'attendance_date', 'task_type', 'description', 'letter_path'];

    protected function casts(): array
    {
        return ['attendance_date' => 'date'];
    }

    public function attendable()
    {
        return $this->morphTo();
    }
}
