<?php

namespace App\Models;

use App\Models\ProcessLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessLogLog extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'process_log_logs';

    protected $fillable = [
        'process_log_id',
        'step',
        'status',
        'message'
    ];

    // Relación con el modelo ProcessLog
    public function processLog(): BelongsTo
    {
        return $this->belongsTo(ProcessLog::class);
    }
}
