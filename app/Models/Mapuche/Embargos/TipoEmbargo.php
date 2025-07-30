<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Embargos;

use App\Models\Dh12;
use App\Models\Mapuche\Embargo;
use App\Models\Mapuche\Emgargos\TipoRemuneracion;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para la tabla mapuche.emb_tipo_embargo.
 *
 * @property int $id_tipo_embargo ID del tipo de embargo (PK)
 * @property string $desc_tipo_embargo Descripción del tipo de embargo
 * @property int $codn_tipogrupo Código de tipo grupo
 * @property int|null $codn_conce Código de concepto
 * @property int $mov_inicial_cta_cte Movimiento inicial cuenta corriente (0,1,2)
 * @property int $id_tipo_remuneracion ID del tipo de remuneración
 *
 * @method static \Database\Factories\TipoEmbargoFactory factory()
 */
class TipoEmbargo extends Model
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
    protected $table = 'mapuche.emb_tipo_embargo';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'id_tipo_embargo';

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'desc_tipo_embargo',
        'codn_tipogrupo',
        'codn_conce',
        'mov_inicial_cta_cte',
        'id_tipo_remuneracion',
    ];

    /**
     * Relación con embargos.
     */
    public function embargos(): HasMany
    {
        return $this->hasMany(Embargo::class, 'id_tipo_embargo');
    }

    /**
     * Relación con tipo de remuneración.
     */
    public function tipoRemuneracion(): BelongsTo
    {
        return $this->belongsTo(TipoRemuneracion::class, 'id_tipo_remuneracion');
    }

    /**
     * Relación con concepto DH12.
     */
    public function concepto(): BelongsTo
    {
        return $this->belongsTo(Dh12::class, 'codn_conce', 'codn_conce');
    }

    /**
     * Reglas de validación para mov_inicial_cta_cte.
     */
    #[\Override]
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model): void {
            if (!\in_array($model->mov_inicial_cta_cte, [0, 1, 2])) {
                throw new \InvalidArgumentException('mov_inicial_cta_cte debe ser 0, 1 o 2');
            }
        });
    }

    /**
     * Casting de atributos.
     */
    protected function casts(): array
    {
        return [
            'id_tipo_embargo' => 'integer',
            'desc_tipo_embargo' => 'string',
            'codn_tipogrupo' => 'integer',
            'codn_conce' => 'integer',
            'mov_inicial_cta_cte' => 'integer',
            'id_tipo_remuneracion' => 'integer',
        ];
    }
}
