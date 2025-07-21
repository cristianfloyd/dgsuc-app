<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Embargos;

use App\Models\Mapuche\Embargo;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Eloquent para la tabla mapuche.emb_tipo_expediente
 *
 * @property int $id_tipo_expediente ID del tipo de expediente (PK)
 * @property string $desc_tipo_expediente Descripción del tipo de expediente
 *
 * @method static \Database\Factories\TipoExpedienteFactory factory()
 */
class TipoExpediente extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'mapuche.emb_tipo_expediente';

    /**
     * Clave primaria
     */
    protected $primaryKey = 'id_tipo_expediente';

    /**
     * Desactivar timestamps de Laravel
     */
    public $timestamps = false;

    /**
     * Atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'desc_tipo_expediente'
    ];

    /**
     * Casting de atributos
     */
    protected $casts = [
        'id_tipo_expediente' => 'integer',
        'desc_tipo_expediente' => 'string'
    ];

    /**
     * Relación con embargos
     */
    public function embargos(): HasMany
    {
        return $this->hasMany(Embargo::class, 'id_tipo_expediente');
    }
}
