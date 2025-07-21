<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Mapuche\Dh35Queries;

/**
 * Modelo para la tabla de Caracteres y Escalafones
 *
 * @property string $tipo_escal Tipo de Escalafón
 * @property string $codc_carac Código de Carácter
 * @property string|null $desc_grupo Descripción del Grupo
 * @property string|null $tipo_carac Tipo Permanente o Transitorio
 * @property int|null $nro_orden Nro. de Orden (0 a 29)
 * @property int|null $nro_subpc Número de Sub PC
 * @property int|null $controlcargos Control de Planta
 * @property int|null $controlhoras Control de Horas
 * @property int|null $controlpuntos Control de Puntos
 * @property bool $caracter_concursado Indica si es concursado
 */
class Dh35 extends Model
{
    use HasFactory;
    use Dh35Queries;

    protected $table = 'mapuche.dh35';

    public $timestamps = false;

    protected $primaryKey = ['tipo_escal', 'codc_carac'];

    public $incrementing = false;

    protected $fillable = [
        'tipo_escal',
        'codc_carac',
        'desc_grupo',
        'tipo_carac',
        'nro_orden',
        'nro_subpc',
        'controlcargos',
        'controlhoras',
        'controlpuntos',
        'caracter_concursado'
    ];

    protected $casts = [
        'tipo_escal' => 'string',
        'codc_carac' => 'string',
        'desc_grupo' => 'string',
        'tipo_carac' => 'string',
        'nro_orden' => 'integer',
        'nro_subpc' => 'integer',
        'controlcargos' => 'integer',
        'controlhoras' => 'integer',
        'controlpuntos' => 'integer',
        'caracter_concursado' => 'boolean'
    ];

    /**
     * Scope para filtrar por tipo de escalafón
     */
    public function scopeTipoEscalafon($query, string $tipoEscal)
    {
        return $query->where('tipo_escal', $tipoEscal);
    }

    /**
     * Scope para caracteres concursados
     */
    public function scopeConcursados($query)
    {
        return $query->where('caracter_concursado', true);
    }
}
