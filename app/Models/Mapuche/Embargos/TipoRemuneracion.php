<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Emgargos;

use App\Models\Mapuche\Embargo;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Eloquent para la tabla mapuche.emb_tipo_remuneracion
 *
 * @property int $id_tipo_remuneracion ID del tipo de remuneración (PK)
 * @property string $desc_tipo_remuneracion Descripción del tipo de remuneración
 *
 * @method static \Database\Factories\TipoRemuneracionFactory factory()
 */
class TipoRemuneracion extends Model
{
    use HasFactory, MapucheConnectionTrait;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'mapuche.emb_tipo_remuneracion';

    /**
     * Clave primaria
     */
    protected $primaryKey = 'id_tipo_remuneracion';

    /**
     * Desactivar timestamps de Laravel
     */
    public $timestamps = false;

    /**
     * Atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'desc_tipo_remuneracion'
    ];

    /**
     * Casting de atributos
     */
    protected $casts = [
        'id_tipo_remuneracion' => 'integer',
        'desc_tipo_remuneracion' => 'string'
    ];

    /**
     * Relación con embargos
     */
    public function embargos(): HasMany
    {
        return $this->hasMany(Embargo::class, 'id_tipo_remuneracion');
    }
}
