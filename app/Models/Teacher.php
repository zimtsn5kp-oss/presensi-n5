<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nip', 'name', 'subject', 'position', 'fingerprint_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function attendanceLogs()
    {
        return $this->morphMany(AttendanceLog::class, 'attendable');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function fingerprintTemplates()
    {
        return $this->morphMany(FingerprintTemplate::class, 'owner');
    }
}
