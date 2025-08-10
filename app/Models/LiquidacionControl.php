<?php

namespace App\Models;

use App\Models\Mapuche\Dh22;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

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

    protected $casts = [
        'datos_resultado' => 'array',
        'fecha_ejecucion' => 'datetime',
    ];

    /**
     * Obtiene el color de estado para badges.
     */
    public function estadoColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->estado) {
                'pendiente' => 'warning',
                'error' => 'danger',
                'completado' => 'success',
                default => 'gray',
            },
        );
    }

    /**
     * Define relación con el modelo de liquidación.
     */
    public function liquidacion(): BelongsTo
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }

    /**
     * Scope para filtrar por número de liquidación.
     */
    public function scopeLiquidacion($query, $nroLiqui)
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    /**
     * Verifica si la tabla existe en la base de datos.
     *
     * @return bool
     */
    public static function tableExists(): bool
    {
        try {
            return Schema::hasTable('suc.controles_liquidacion');
        } catch (\Exception $e) {
            return false;
        }
    }
}
