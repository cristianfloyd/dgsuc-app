<?php

namespace App\Models;

use App\Models\Mapuche\Dh22;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CopyJob extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.copy_jobs';

    protected $fillable = [
        'user_id',
        'source_table',
        'target_table',
        'nro_liqui',
        'total_records',
        'copied_records',
        'status',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected $casts = [
        'total_records' => 'integer',
        'copied_records' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * Relación con la liquidación (Dh22) asociada a este CopyJob.
     *
     * @return BelongsTo Relación BelongsTo con el modelo Dh22 usando el campo nro_liqui.
     */
    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }
}
