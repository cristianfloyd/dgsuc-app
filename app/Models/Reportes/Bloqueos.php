<?php

namespace App\Models\Reportes;

use App\Models\Dh01;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bloqueos extends Model
{
    use SoftDeletes;
    use MapucheConnectionTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'suc.rep_bloqueos';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nro_liqui',
        'fecha_registro',
        'email',
        'nombre',
        'usuario_mapuche',
        'dependencia',
        'nro_legaj',
        'nro_cargo',
        'fecha_baja',
        'tipo',
        'observaciones',
        'chkstopliq',
        'estado',
        'mensaje_error',
        'datos_validacion',
        'fecha_procesamiento',
        'procesado_por',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_registro' => 'datetime',
        'fecha_baja' => 'date',
        'chkstopliq' => 'boolean',
        'datos_validacion' => 'json',
        'fecha_procesamiento' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope para filtrar por número de liquidación
     */
    public function scopePorLiquidacion(Builder $query, int $nroLiqui): Builder
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    /**
     * Scope para filtrar por legajo
     */
    public function scopePorLegajo(Builder $query, int $legajo): Builder
    {
        return $query->where('nro_legaj', $legajo);
    }

    /**
     * Scope para filtrar por tipo de bloqueo
     */
    public function scopePorTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopePorEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para obtener los bloqueos pendientes de procesar
     */
    public function scopePendientes(Builder $query): Builder
    {
        return $query->whereNull('fecha_procesamiento');
    }

    /**
     * Scope para obtener los bloqueos procesados
     */
    public function scopeProcesados(Builder $query): Builder
    {
        return $query->whereNotNull('fecha_procesamiento');
    }

    /**
     * Scope para filtrar por rango de fechas de registro
     */
    public function scopePorRangoFechaRegistro(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha_registro', [$desde, $hasta]);
    }

    /**
     * Scope para filtrar bloqueos activos (con chkstopliq = true)
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('chkstopliq', true);
    }

    // ############################################################
    // ###################### RELACIONES ##########################
    // ############################################################

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }
}
