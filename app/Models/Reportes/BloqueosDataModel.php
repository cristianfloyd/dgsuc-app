<?php

namespace App\Models\Reportes;

use App\Enums\BloqueosEstadoEnum;
use App\Enums\LegajoCargo;
use App\Models\Dh01;
use App\Models\Dh03;
use App\Models\Dh90;
use App\Traits\MapucheConnectionTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

use function in_array;

class BloqueosDataModel extends Model
{
    use MapucheConnectionTrait;
    use HasFactory;

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

    public function validarEstado(): void
    {
        // Primero verificamos si ya existe un registro con el mismo par legajo-cargo
        $duplicado = self::query()->where('id', '!=', $this->id)
            ->where('nro_legaj', $this->nro_legaj)
            ->where('nro_cargo', $this->nro_cargo)
            ->exists();

        if ($duplicado) {
            $this->estado = BloqueosEstadoEnum::DUPLICADO;
            $this->mensaje_error = 'Ya existe un registro con el mismo par legajo-cargo';
        }
        // Si no es duplicado, verificamos si existe en Mapuche
        elseif (Dh03::validarParLegajoCargo($this->nro_legaj, $this->nro_cargo)) {
            // Obtenemos el cargo para comparar fechas y estado
            $cargo = Dh03::buscarPorLegajoCargo($this->nro_legaj, $this->nro_cargo)->first();

            // Caso especial para licencias: verificar si ya está bloqueado
            if ($this->tipo === 'licencia' && $cargo->chkstopliq == 1) {
                $this->estado = BloqueosEstadoEnum::LICENCIA_YA_BLOQUEADA;
                $this->mensaje_error = 'El cargo ya tiene el stop de liquidación activado';
            }
            // Si el tipo es fallecido o renuncia y tiene fecha de baja, comparamos con la fecha del cargo
            elseif (in_array($this->tipo, ['fallecido', 'renuncia']) && $this->fecha_baja && $cargo->fec_baja) {
                $fechaBajaImportada = \Illuminate\Support\Facades\Date::parse($this->fecha_baja);
                $fechaBajaCargo = \Illuminate\Support\Facades\Date::parse($cargo->fec_baja);

                if ($fechaBajaImportada->eq($fechaBajaCargo)) {
                    $this->estado = BloqueosEstadoEnum::FECHAS_COINCIDENTES;
                    $this->mensaje_error = 'La fecha de baja coincide con la registrada en Mapuche';
                } elseif ($fechaBajaImportada->gt($fechaBajaCargo)) {
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
                $fechaBajaImportada = $this->fecha_baja ? \Illuminate\Support\Facades\Date::parse($this->fecha_baja) : null;
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
     * Verifica y actualiza si el legajo tiene cargo asociado en Mapuche.
     */
    public function verificarCargoAsociado(): void
    {
        $tieneCargoAsociado = Dh90::query()->where('nro_cargo', $this->nro_cargo)
            ->whereNotNull('nro_cargoasociado')
            ->exists();

        $this->tiene_cargo_asociado = $tieneCargoAsociado;
        $this->save();
    }

    /* ####################################################################################################
       ###########################################  RELACIONES ############################################### */
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dh01, $this>
     */
    public function dh01(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dh03, $this>
     */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Dh03::class, 'nro_cargo', 'nro_cargo');
    }

    /**
     * Obtiene la información del cargo asociado desde la tabla dh90.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dh90, $this>
     */
    public function cargoAsociado(): BelongsTo
    {
        return $this->belongsTo(Dh90::class, 'nro_cargo', 'nro_cargo');
    }

    /* ######## ATTRIBUTES ########################################## */
    protected function legajoCargo(): Attribute
    {
        return Attribute::make(
            get: fn(): \App\Enums\LegajoCargo => LegajoCargo::from($this->nro_legaj, $this->nro_cargo),
        );
    }

    protected function fechaBaja(): Attribute
    {
        return Attribute::make(
            get: fn($value): ?string => $value ? \Illuminate\Support\Facades\Date::parse($value)->format('Y-m-d') : null,
            set: fn($value): ?string => $value ? \Illuminate\Support\Facades\Date::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function fechasCoincidentes(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (): bool {
            if (!$this->fecha_baja || !$this->cargo?->fec_baja) {
                return false;
            }
            return \Illuminate\Support\Facades\Date::parse($this->fecha_baja)->format('Y-m-d')
                === \Illuminate\Support\Facades\Date::parse($this->cargo->fec_baja)->format('Y-m-d');
        });
    }

    /* ################## SCOPES ##################################### */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function fechasCoinciden($query)
    {
        return $query->whereRaw('DATE(fecha_baja) = DATE(fec_baja)');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function tipo($query, $tipo)
    {
        return $query->where('tipo', strtolower((string) $tipo));
    }

    #[Override]
    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * Valida que las fechas de baja del cargo principal y su asociado coincidan.
     *
     * @param Carbon|null $fechaBajaImportada La fecha de baja del cargo principal
     *
     * @return array|null Array con estado y mensaje si hay error, null si pasa la validación
     */
    protected function validarFechasCargoAsociado(?Carbon $fechaBajaImportada): ?array
    {
        // Si no tiene cargo asociado, no hay nada que validar
        if (!$this->tiene_cargo_asociado) {
            return null;
        }

        // Obtener información del cargo asociado
        $cargoAsociadoInfo = Dh90::query()->where('nro_cargo', $this->nro_cargo)
            ->whereNotNull('nro_cargoasociado')
            ->first();

        if (!$cargoAsociadoInfo) {
            return null;
        }

        $nroCargoAsociado = $cargoAsociadoInfo->nro_cargoasociado;

        // Verificar si el cargo asociado está en la tabla de bloqueos
        $cargoAsociadoEnBloqueos = self::query()->where('nro_cargo', $nroCargoAsociado)
            ->where('id', '!=', $this->id)
            ->exists();

        if (!$cargoAsociadoEnBloqueos) {
            return [
                'estado' => BloqueosEstadoEnum::FALTA_CARGO_ASOCIADO,
                'mensaje' => "El cargo asociado #{$nroCargoAsociado} no está incluido en los bloqueos",
            ];
        }

        // Verificar que las fechas de baja coincidan si ambos tienen fecha de baja
        $cargoAsociadoBloqueo = self::query()->where('nro_cargo', $nroCargoAsociado)->first();

        if ($cargoAsociadoBloqueo && $fechaBajaImportada && $cargoAsociadoBloqueo->fecha_baja) {
            $fechaBajaCargoAsociado = \Illuminate\Support\Facades\Date::parse($cargoAsociadoBloqueo->fecha_baja);

            if (!$fechaBajaImportada->eq($fechaBajaCargoAsociado)) {
                return [
                    'estado' => BloqueosEstadoEnum::FECHA_CARGO_NO_COINCIDE,
                    'mensaje' => "La fecha de baja del cargo principal ({$fechaBajaImportada->format('Y-m-d')}) "
                        . "no coincide con la del cargo asociado #{$nroCargoAsociado} ({$fechaBajaCargoAsociado->format('Y-m-d')})",
                ];
            }
        }

        // Si pasa todas las validaciones
        return null;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'fecha_registro' => 'datetime',
            'fecha_baja' => 'date:Y-m-d',
            'chkstopliq' => 'boolean',
            'fec_baja' => 'date:Y-m-d',
            'estado' => BloqueosEstadoEnum::class,
            'tiene_cargo_asociado' => 'boolean',
            'esta_procesado' => 'boolean',
        ];
    }
}
