<?php

namespace App\Models\Mapuche\Catalogo;

use App\Models\Dh03;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent para la tabla 'mapuche.dh36' que representa los datos de dependencias de empleados.
 *
 * @property string $coddependesemp Código único de la dependencia del empleado.
 * @property string $cordinadorcontrato Coordinador del contrato.
 * @property string $descdependesemp Descripción de la dependencia del empleado.
 * @property string $cod_organismo Código del organismo.
 * @property string $cod_organismo_eval Código del organismo evaluador.
 * @property string $cod_ubic_geografica_sirhu Código de la ubicación geográfica en el sistema SIRHU.
 *
 * @property \App\Models\Mapuche\Catalogo\Dhe4 $organismo Relación con el modelo Dhe4 (organismo).
 * @property \App\Models\Mapuche\Catalogo\Dhe4 $organismoEvaluador Relación con el modelo Dhe4 (organismo evaluador).
 */
class Dh36 extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'mapuche.dh36';
    protected $primaryKey = 'coddependesemp';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'coddependesemp',
        'cordinadorcontrato',
        'descdependesemp',
        'cod_organismo',
        'cod_organismo_eval',
        'cod_ubic_geografica_sirhu',
    ];

    /**
     * Relación con el modelo Dhe4 (organismo).
     */
    public function organismo(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo', 'cod_organismo');
    }

    /**
     * Relación con el modelo Dhe4 (organismo evaluador).
     */
    public function organismoEvaluador(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo_eval', 'cod_organismo');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Dh03::class, 'coddependesemp', 'coddependesemp');
    }

    public function dhe4(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo', 'cod_organismo');
    }
}
