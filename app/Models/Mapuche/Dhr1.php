<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Models\Mapuche\Dh22;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\AsStringable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Dhr1 para gestión de liquidaciones Mapuche
 *
 * @property int $nro_liqui Número de liquidación (Primary Key)
 * @property int|null $per_liano Año del período
 * @property int|null $per_limes Mes del período
 * @property string|null $desc_liqui Descripción de liquidación
 * @property \DateTime|null $fec_emisi Fecha de emisión
 * @property \DateTime|null $fec_ultap Fecha último aporte
 * @property int|null $per_anoap Año del período de aporte
 * @property int|null $per_mesap Mes del período de aporte
 * @property string|null $desc_lugap Descripción lugar de aporte
 * @property string|null $plantilla UUID de la plantilla
 */
class Dhr1 extends Model
{
    use HasFactory;
    use HasUuids;
    use MapucheConnectionTrait;


    /**
     * Esquema y tabla específicos
     */
    protected $table = 'dhr1';

    /**
     * Clave primaria
     */
    protected $primaryKey = 'nro_liqui';

    /**
     * Deshabilitar timestamps de Laravel
     */
    public $timestamps = false;

    /**
     * Atributos asignables masivamente
     */
    protected $fillable = [
        'per_liano',
        'per_limes',
        'desc_liqui',
        'fec_emisi',
        'fec_ultap',
        'per_anoap',
        'per_mesap',
        'desc_lugap',
        'plantilla'
    ];

    /**
     * Casting de atributos
     */
    protected $casts = [
        'nro_liqui' => 'integer',
        'per_liano' => 'integer',
        'per_limes' => 'integer',
        'desc_liqui' => 'string',
        'fec_emisi' => 'date',
        'fec_ultap' => 'date',
        'per_anoap' => 'integer',
        'per_mesap' => 'integer',
        'desc_lugap' => 'string',
        'plantilla' => AsStringable::class,
    ];

    /**
     * Relación con la tabla dh22
     */
    public function dh22(): BelongsTo
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }

    protected function plantilla(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? (string)$value : null,
            set: fn ($value) => $value ? (string)$value : null
        );
    }

    public function uniqueIds(): array
    {
        return ['plantilla'];
    }
}
