<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Embargos;

use App\Models\Mapuche\Embargo;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para la tabla mapuche.emb_tipo_juicio.
 *
 * @property int $id_tipo_juicio ID del tipo de juicio (PK)
 * @property string $desc_tipo_juicio Descripción del tipo de juicio
 *
 * @method static \Database\Factories\TipoJuicioFactory factory()
 */
class TipoJuicio extends Model
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
    protected $table = 'mapuche.emb_tipo_juicio';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_tipo_juicio';

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'desc_tipo_juicio',
    ];

    /**
     * Casting de atributos.
     */
    protected $casts = [
        'id_tipo_juicio' => 'integer',
        'desc_tipo_juicio' => 'string',
    ];

    /**
     * Relación con embargos.
     */
    public function embargos(): HasMany
    {
        return $this->hasMany(Embargo::class, 'id_tipo_juicio');
    }
}
