<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function record(string $event, ?Model $subject = null, array $properties = []): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => request()?->ip(),
            'properties' => $properties,
        ]);
    }
}
