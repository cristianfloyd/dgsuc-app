<?php

namespace App\Models\Reportes;

use App\Models\Dh01;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
     * Scope para filtrar por número de liquidación.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function porLiquidacion(Builder $query, int $nroLiqui): Builder
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    /**
     * Scope para filtrar por legajo.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function porLegajo(Builder $query, int $legajo): Builder
    {
        return $query->where('nro_legaj', $legajo);
    }

    /**
     * Scope para filtrar por tipo de bloqueo.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function porTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para filtrar por estado.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function porEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para obtener los bloqueos pendientes de procesar.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function pendientes(Builder $query): Builder
    {
        return $query->whereNull('fecha_procesamiento');
    }

    /**
     * Scope para obtener los bloqueos procesados.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function procesados(Builder $query): Builder
    {
        return $query->whereNotNull('fecha_procesamiento');
    }

    /**
     * Scope para filtrar por rango de fechas de registro.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function porRangoFechaRegistro(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha_registro', [$desde, $hasta]);
    }

    /**
     * Scope para filtrar bloqueos activos (con chkstopliq = true).
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function activos(Builder $query): Builder
    {
        return $query->where('chkstopliq', true);
    }

    // ############################################################
    // ###################### RELACIONES ##########################
    // ############################################################
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dh01, $this>
     */
    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }
    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'fecha_registro' => 'datetime',
            'fecha_baja' => 'date',
            'chkstopliq' => 'boolean',
            'datos_validacion' => 'json',
            'fecha_procesamiento' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
