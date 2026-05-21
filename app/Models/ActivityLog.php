<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'event', 'subject_type', 'subject_id', 'ip_address', 'properties'];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }
}
