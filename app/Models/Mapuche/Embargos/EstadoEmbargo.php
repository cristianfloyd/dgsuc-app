<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Embargos;

use App\Models\Mapuche\Embargo;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para la tabla mapuche.emb_estado_embargo.
 *
 * @property int $id_estado_embargo ID del estado de embargo (PK)
 * @property string $desc_estado_embargo Descripción del estado
 *
 * @method static \Database\Factories\EstadoEmbargoFactory factory()
 */
class EstadoEmbargo extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Desactivar timestamps de Laravel.
     */
    public $timestamps = false;

    /**
     * Nombre de la tabla en la base de datos.
     */
    protected $table = 'mapuche.emb_estado_embargo';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_estado_embargo';

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'desc_estado_embargo',
    ];

    /**
     * Casting de atributos.
     */
    protected $casts = [
        'id_estado_embargo' => 'integer',
        'desc_estado_embargo' => 'string',
    ];

    /**
     * Relación con embargos.
     */
    public function embargos(): HasMany
    {
        return $this->hasMany(Embargo::class, 'id_estado_embargo');
    }
}
