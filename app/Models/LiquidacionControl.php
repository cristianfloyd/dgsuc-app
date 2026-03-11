<?php

namespace App\Models;

use App\Models\Mapuche\Dh22;
use App\Traits\MapucheConnectionTrait;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Override;

class LiquidacionControl extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.controles_liquidacion';

    protected $fillable = [
        'nombre_control',
        'descripcion',
        'estado',
        'resultado',
        'datos_resultado',
        'nro_liqui',
        'fecha_ejecucion',
        'ejecutado_por',
    ];

    /**
     * Define relación con el modelo de liquidación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Mapuche\Dh22, $this>
     */
    public function liquidacion(): BelongsTo
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }

    /**
     * Verifica si la tabla existe en la base de datos.
     */
    public static function tableExists(): bool
    {
        try {
            return Schema::hasTable('suc.controles_liquidacion');
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Obtiene el color de estado para badges.
     */
    protected function estadoColor(): Attribute
    {
        return Attribute::make(
            get: fn(): string => match ($this->estado) {
                'pendiente' => 'warning',
                'error' => 'danger',
                /** @phpstan-ignore-next-line match.alwaysTrue */
                'completado' => 'success',
                default => 'gray',
            },
        );
    }

    /**
     * Scope para filtrar por número de liquidación.
     */
    protected function scopeLiquidacion($query, $nroLiqui)
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'datos_resultado' => 'array',
            'fecha_ejecucion' => 'datetime',
        ];
    }
}
