<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsencePermission extends Model
{
    protected $fillable = ['attendable_type', 'attendable_id', 'created_by', 'absence_date', 'type', 'description', 'attachment_path'];

    protected function casts(): array
    {
        return ['absence_date' => 'date'];
    }

    public function attendable()
    {
        return $this->morphTo();
    }
}
