<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fingerprint extends Model
{
    protected $fillable = ['name', 'ip_address', 'port', 'serial_number', 'is_online', 'last_sync_at', 'machine_user_count', 'settings'];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'last_sync_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function histories()
    {
        return $this->hasMany(FingerprintSyncHistory::class);
    }
}
