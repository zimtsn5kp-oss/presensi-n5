<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = ['user_id', 'file_name', 'disk', 'size', 'type', 'restored_at'];

    protected function casts(): array
    {
        return ['restored_at' => 'datetime'];
    }
}
