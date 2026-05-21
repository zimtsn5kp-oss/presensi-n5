<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = ['fingerprint_id', 'attendable_type', 'attendable_id', 'machine_user_id', 'attendance_date', 'check_in_at', 'check_out_at', 'status', 'late_minutes', 'source', 'note', 'raw_payload'];

    protected function casts(): array
    {
        return ['attendance_date' => 'date', 'raw_payload' => 'array'];
    }

    public function attendable()
    {
        return $this->morphTo();
    }

    public function fingerprint()
    {
        return $this->belongsTo(Fingerprint::class);
    }
}
