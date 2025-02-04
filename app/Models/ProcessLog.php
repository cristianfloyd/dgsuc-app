<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\MapucheConnectionTrait;
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

    /**
     * Obtiene una relación HasMany con los registros de registro de procesos que tienen este registro de registro de proceso como padre.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ProcessLog::class, 'parent_id');
    }
}
