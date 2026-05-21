<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FingerprintTemplate extends Model
{
    protected $fillable = ['fingerprint_id', 'owner_type', 'owner_id', 'machine_user_id', 'finger_index', 'template_data', 'checksum', 'backed_up_at'];

    protected function casts(): array
    {
        return ['backed_up_at' => 'datetime'];
    }

    public function owner()
    {
        return $this->morphTo();
    }
}
