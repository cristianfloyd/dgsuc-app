<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla de Grupos de Conceptos del schema Mapuche.
 *
 * @property int $codn_grupo Identificador único del grupo
 * @property string $desc_grupo Descripción del grupo
 * @property int $codn_tipogrupo Identificador del tipo de grupo
 *
 * @method static Builder|self porTipoGrupo(int $tipoGrupo)
 */
class Dh15 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Indicamos que no use timestamps.
     */
    public $timestamps = false;

    /**
     * Nombre real de la tabla en la base de datos.
     */
    protected $table = 'dh15';

    /**
     * Campos que pueden ser asignados masivamente.
     */
    protected $fillable = [
        'codn_grupo',
        'desc_grupo',
        'codn_tipogrupo',
    ];

    /**
     * Casteos de atributos.
     */
    protected $casts = [
        'codn_grupo' => 'integer',
        'desc_grupo' => 'string',
        'codn_tipogrupo' => 'integer',
    ];

    /**
     * Scope para filtrar por tipo de grupo.
     */
    public function scopePorTipoGrupo(Builder $query, int $tipoGrupo): Builder
    {
        return $query->where('codn_tipogrupo', $tipoGrupo);
    }
}
