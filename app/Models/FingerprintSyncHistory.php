<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FingerprintSyncHistory extends Model
{
    protected $fillable = ['fingerprint_id', 'user_id', 'action', 'status', 'processed_rows', 'message', 'payload'];

    public function fingerprint()
    {
        return $this->belongsTo(Fingerprint::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }
}
