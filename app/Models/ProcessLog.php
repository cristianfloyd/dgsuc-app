<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessLog extends Model
{
    protected $fillable = [
        'process_name',
        'status',
        'steps',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'steps' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
