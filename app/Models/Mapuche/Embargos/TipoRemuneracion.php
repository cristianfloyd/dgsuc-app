<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Embargos;

use App\Models\Mapuche\Embargo;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para la tabla mapuche.emb_tipo_remuneracion.
 *
 * @property int $id_tipo_remuneracion ID del tipo de remuneración (PK)
 * @property string $desc_tipo_remuneracion Descripción del tipo de remuneración
 */
class TipoRemuneracion extends Model
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
    protected $table = 'mapuche.emb_tipo_remuneracion';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_tipo_remuneracion';

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'desc_tipo_remuneracion',
    ];

    /**
     * Relación con embargos.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapuche\Embargo, $this>
     */
    public function embargos(): HasMany
    {
        return $this->hasMany(Embargo::class, 'id_tipo_remuneracion');
    }

    /**
     * Casting de atributos.
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'id_tipo_remuneracion' => 'integer',
            'desc_tipo_remuneracion' => 'string',
        ];
    }
}
