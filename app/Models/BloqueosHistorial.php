<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reportes\BloqueosDataModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueosHistorial extends Model
{
    use MapucheConnectionTrait;
    use SoftDeletes;

    protected $table = 'suc.bloqueos_historial';

    protected $fillable = [
        'periodo_importacion',
        'bloqueo_id',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'usuario_id',
        'estado_procesamiento',
        'resultado_final',
        'metadata'
    ];

    protected $casts = [
        'periodo_importacion' => 'date',
        'resultado_final' => 'boolean',
        'metadata' => 'array'
    ];

    // Relaciones
    public function bloqueo(): BelongsTo
    {
        return $this->belongsTo(BloqueosDataModel::class, 'bloqueo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
