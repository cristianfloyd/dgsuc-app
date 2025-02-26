<?php

namespace App\Models\Reportes;

use Carbon\Carbon;
use App\Models\Dh03;
use App\Models\Dh90;
use App\Enums\LegajoCargo;
use App\Enums\BloqueosEstadoEnum;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BloqueosDataModel extends Model
{
    use MapucheConnectionTrait, HasFactory;

    protected $table = 'suc.rep_bloqueos_import';
    protected $primaryKey = 'id';

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
        'tiene_cargo_asociado',
        'esta_procesado',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'fecha_baja' => 'date:Y-m-d',
        'chkstopliq' => 'boolean',
        'fec_baja' => 'date:Y-m-d',
        'estado' => BloqueosEstadoEnum::class,
        'tiene_cargo_asociado' => 'boolean',
        'esta_procesado' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    public function validarEstado(): void
    {
        // Primero verificamos si ya existe un registro con el mismo par legajo-cargo
        $duplicado = self::where('id', '!=', $this->id)
            ->where('nro_legaj', $this->nro_legaj)
            ->where('nro_cargo', $this->nro_cargo)
            ->exists();

        if ($duplicado) {
            $this->estado = BloqueosEstadoEnum::DUPLICADO;
            $this->mensaje_error = 'Ya existe un registro con el mismo par legajo-cargo';
        }
        // Si no es duplicado, verificamos si existe en Mapuche
        else if (Dh03::validarParLegajoCargo($this->nro_legaj, $this->nro_cargo)) {
            // Obtenemos el cargo para comparar fechas y estado
            $cargo = Dh03::buscarPorLegajoCargo($this->nro_legaj, $this->nro_cargo)->first();

            // Caso especial para licencias: verificar si ya está bloqueado
            if ($this->tipo === 'licencia' && $cargo->chkstopliq == 1) {
                $this->estado = BloqueosEstadoEnum::LICENCIA_YA_BLOQUEADA;
                $this->mensaje_error = 'El cargo ya tiene el stop de liquidación activado';
            }
            // Si el tipo es fallecido o renuncia y tiene fecha de baja, comparamos con la fecha del cargo
            else if (in_array($this->tipo, ['fallecido', 'renuncia']) && $this->fecha_baja && $cargo->fec_baja) {
                $fechaBajaImportada = Carbon::parse($this->fecha_baja);
                $fechaBajaCargo = Carbon::parse($cargo->fec_baja);

                if ($fechaBajaImportada->eq($fechaBajaCargo)) {
                    $this->estado = BloqueosEstadoEnum::FECHAS_COINCIDENTES;
                    $this->mensaje_error = 'La fecha de baja coincide con la registrada en Mapuche';
                } else if ($fechaBajaImportada->gt($fechaBajaCargo)) {
                    $this->estado = BloqueosEstadoEnum::FECHA_SUPERIOR;
                    $this->mensaje_error = 'La fecha de baja es posterior a la registrada en Mapuche';
                } else {
                    // Verificamos el cargo asociado
                    $this->verificarCargoAsociado();

                    // Validamos el cargo asociado
                    $resultadoValidacion = $this->validarFechasCargoAsociado($fechaBajaImportada);

                    if ($resultadoValidacion) {
                        $this->estado = $resultadoValidacion['estado'];
                        $this->mensaje_error = $resultadoValidacion['mensaje'];
                    } else {
                        // Si pasa todas las validaciones
                        $this->estado = BloqueosEstadoEnum::VALIDADO;
                        $this->mensaje_error = null;
                    }
                }
            } else {
                // Verificamos el cargo asociado
                $this->verificarCargoAsociado();

                // Validamos el cargo asociado
                $fechaBajaImportada = $this->fecha_baja ? Carbon::parse($this->fecha_baja) : null;
                $resultadoValidacion = $this->validarFechasCargoAsociado($fechaBajaImportada);

                if ($resultadoValidacion) {
                    $this->estado = $resultadoValidacion['estado'];
                    $this->mensaje_error = $resultadoValidacion['mensaje'];
                } else {
                    // Si pasa todas las validaciones
                    $this->estado = BloqueosEstadoEnum::VALIDADO;
                    $this->mensaje_error = null;
                }
            }
        } else {
            $this->estado = BloqueosEstadoEnum::ERROR_VALIDACION;
            $this->mensaje_error = 'Par legajo-cargo no encontrado en Mapuche';
        }

        $this->save();
    }

    /**
     * Valida que las fechas de baja del cargo principal y su asociado coincidan
     *
     * @param Carbon|null $fechaBajaImportada La fecha de baja del cargo principal
     * @return array|null Array con estado y mensaje si hay error, null si pasa la validación
     */
    protected function validarFechasCargoAsociado(?Carbon $fechaBajaImportada): ?array
    {
        // Si no tiene cargo asociado, no hay nada que validar
        if (!$this->tiene_cargo_asociado) {
            return null;
        }

        // Obtener información del cargo asociado
        $cargoAsociadoInfo = Dh90::where('nro_cargo', $this->nro_cargo)
            ->whereNotNull('nro_cargoasociado')
            ->first();

        if (!$cargoAsociadoInfo) {
            return null;
        }

        $nroCargoAsociado = $cargoAsociadoInfo->nro_cargoasociado;

        // Verificar si el cargo asociado está en la tabla de bloqueos
        $cargoAsociadoEnBloqueos = self::where('nro_cargo', $nroCargoAsociado)
            ->where('id', '!=', $this->id)
            ->exists();

        if (!$cargoAsociadoEnBloqueos) {
            return [
                'estado' => BloqueosEstadoEnum::FALTA_CARGO_ASOCIADO,
                'mensaje' => "El cargo asociado #{$nroCargoAsociado} no está incluido en los bloqueos"
            ];
        }

        // Verificar que las fechas de baja coincidan si ambos tienen fecha de baja
        $cargoAsociadoBloqueo = self::where('nro_cargo', $nroCargoAsociado)->first();

        if ($cargoAsociadoBloqueo && $fechaBajaImportada && $cargoAsociadoBloqueo->fecha_baja) {
            $fechaBajaCargoAsociado = Carbon::parse($cargoAsociadoBloqueo->fecha_baja);

            if (!$fechaBajaImportada->eq($fechaBajaCargoAsociado)) {
                return [
                    'estado' => BloqueosEstadoEnum::FECHA_CARGO_NO_COINCIDE,
                    'mensaje' => "La fecha de baja del cargo principal ({$fechaBajaImportada->format('Y-m-d')}) no coincide con la del cargo asociado #{$nroCargoAsociado} ({$fechaBajaCargoAsociado->format('Y-m-d')})"
                ];
            }
        }

        // Si pasa todas las validaciones
        return null;
    }

    /**
     * Verifica y actualiza si el legajo tiene cargo asociado en Mapuche
     */
    public function verificarCargoAsociado(): void
    {
        $tieneCargoAsociado = Dh90::where('nro_cargo', $this->nro_cargo)
            ->whereNotNull('nro_cargoasociado')
            ->exists();

        $this->tiene_cargo_asociado = $tieneCargoAsociado;
        $this->save();
    }

    /* ######## ATTRIBUTES ########################################## */
    public function legajoCargo(): Attribute
    {
        return Attribute::make(
            get: fn() => LegajoCargo::from($this->nro_legaj, $this->nro_cargo),
        );
    }

    public function fechaBaja(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
            set: fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /* ######## ACCESORS ########################################### */
    public function getFechasCoincidentesAttribute(): bool
    {
        if (!$this->fecha_baja || !$this->cargo?->fec_baja) {
            return false;
        }

        return Carbon::parse($this->fecha_baja)->format('Y-m-d') ===
            Carbon::parse($this->cargo->fec_baja)->format('Y-m-d');
    }

    /* ##############################################################
    ####  RELACIONES ############################################### */

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Dh03::class, 'nro_cargo', 'nro_cargo');
    }

    /**
     * Obtiene la información del cargo asociado desde la tabla dh90
     */
    public function cargoAsociado(): BelongsTo
    {
        return $this->belongsTo(Dh90::class, 'nro_cargo', 'nro_cargo');
    }

    /* ################## SCOPES ##################################### */
    public function scopeFechasCoinciden($query)
    {
        return $query->whereRaw('DATE(fecha_baja) = DATE(fec_baja)');
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', strtolower($tipo));
    }
}
