<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Models\Dh01;
use App\Models\Mapuche\Catalogo\Dh30;
use App\Traits\Mapuche\Dh09Queries;
use App\Traits\MapucheConnectionTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Modelo para la tabla de Otros Datos del Empleado (DH09).
 *
 * Esta clase representa los datos adicionales de empleados en el sistema Mapuche,
 * incluyendo información sobre estado civil, jubilación, obra social, y otros
 * datos relevantes para la gestión de recursos humanos.
 *
 * @property int $nro_legaj Número de legajo (Clave primaria)
 * @property int|null $vig_otano Año de vigencia de otros datos
 * @property int|null $vig_otmes Mes de vigencia de otros datos
 * @property int|null $nro_tab02 Número de Tabla Múltiple
 * @property string|null $codc_estcv Código estado civil
 * @property bool $sino_embargo Permite neto menor que salario familiar [S/N]
 * @property string|null $sino_otsal Salario familiar en otro organismo [S/N]
 * @property string|null $sino_jubil Jubilado [S/N]
 * @property int|null $nro_tab08 Referencia a dh30
 * @property string|null $codc_bprev Tipo de beneficio previsional
 * @property int|null $nro_tab09 Referencia a dh30
 * @property string|null $codc_obsoc Código obra social
 * @property string|null $nro_afili Número afiliado
 * @property Carbon|null $fec_altos Fecha de alta obra social
 * @property Carbon|null $fec_endjp Fecha de envío de la declaración jurada
 * @property string|null $desc_envio Descripción envío de la declaración jurada
 * @property int|null $cant_cargo Cantidad familiares a cargo
 * @property string|null $desc_tarea Descripción tarea
 * @property string|null $codc_regio Regional de la dependencia de cabecera
 * @property string|null $codc_uacad Dependencia de cabecera
 * @property Carbon|null $fec_vtosf Fecha vencimiento aptitud psicofísica
 * @property Carbon|null $fec_reasf Fecha realización aptitud psicofísica
 * @property Carbon|null $fec_defun Fecha de defunción
 * @property Carbon|null $fecha_jubilacion Fecha de jubilación
 * @property Carbon|null $fecha_grado Fecha de grado
 * @property int|null $nro_agremiacion Número de agremiación
 * @property Carbon|null $fecha_permanencia Fecha de permanencia
 * @property string|null $ua_asigfamiliar Dependencia asignaciones familiares
 * @property Carbon|null $fechadjur894 Fecha declaración jurada decreto 894/01
 * @property string|null $renunciadj894 Cargo (C), Jubilación (J)
 * @property Carbon|null $fechadechere Fecha declaración de herederos
 * @property string|null $coddependesemp Código dependencia de desempeño
 * @property int|null $conyugedependiente Cónyuge en relación de dependencia [S/N]
 * @property Carbon|null $fec_ingreso Fecha de ingreso del agente
 * @property string|null $codc_uacad_seguro Dependencia seguro obligatorio
 * @property Carbon|null $fecha_recibo Fecha para recibo de haberes
 * @property string|null $tipo_norma Tipo de norma aprobatoria
 * @property int|null $nro_norma Número de norma aprobatoria
 * @property string|null $tipo_emite Tipo emisor norma aprobatoria
 * @property Carbon|null $fec_norma Fecha norma aprobatoria
 * @property bool $fuerza_reparto Fuerza reparto para decreto 313/07
 */
class Dh09 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;
    use Dh09Queries;

    public $timestamps = false;

    public $incrementing = false;

    // Configuración básica del modelo
    protected $table = 'mapuche.dh09';

    protected $primaryKey = 'nro_legaj';

    protected $keyType = 'int';

    /**
     * Campos que pueden ser asignados masivamente
     * Organizados por categorías para mejor legibilidad.
     */
    protected $fillable = [
        // Datos de vigencia
        'vig_otano',
        'vig_otmes',

        // Referencias a tablas
        'nro_tab02',
        'nro_tab08',
        'nro_tab09',

        // Estado civil y personal
        'codc_estcv',
        'sino_embargo',
        'sino_otsal',
        'sino_jubil',

        // Beneficios previsionales
        'codc_bprev',

        // Obra social
        'codc_obsoc',
        'nro_afili',
        'fec_altos',

        // Declaraciones juradas
        'fec_endjp',
        'desc_envio',
        'fechadjur894',
        'renunciadj894',
        'fechadechere',

        // Información familiar y laboral
        'cant_cargo',
        'desc_tarea',
        'conyugedependiente',

        // Dependencias y regiones
        'codc_regio',
        'codc_uacad',
        'coddependesemp',
        'ua_asigfamiliar',
        'codc_uacad_seguro',

        // Fechas importantes
        'fec_vtosf',
        'fec_reasf',
        'fec_defun',
        'fecha_jubilacion',
        'fecha_grado',
        'fecha_permanencia',
        'fec_ingreso',
        'fecha_recibo',

        // Agremiación
        'nro_agremiacion',

        // Normas
        'tipo_norma',
        'nro_norma',
        'tipo_emite',
        'fec_norma',

        // Configuraciones especiales
        'fuerza_reparto',
    ];

    /**
     * Configuración de casting de tipos de datos
     * Organizado por tipos para mejor mantenimiento.
     */
    protected $casts = [
        // Enteros
        'nro_legaj' => 'integer',
        'vig_otano' => 'integer',
        'vig_otmes' => 'integer',
        'nro_tab02' => 'integer',
        'nro_tab08' => 'integer',
        'nro_tab09' => 'integer',
        'cant_cargo' => 'integer',
        'nro_agremiacion' => 'integer',
        'conyugedependiente' => 'integer',
        'nro_norma' => 'integer',

        // Strings
        'codc_estcv' => 'string',
        'sino_otsal' => 'string',
        'sino_jubil' => 'string',
        'codc_bprev' => 'string',
        'codc_obsoc' => 'string',
        'nro_afili' => 'string',
        'desc_envio' => 'string',
        'desc_tarea' => 'string',
        'codc_regio' => 'string',
        'codc_uacad' => 'string',
        'ua_asigfamiliar' => 'string',
        'renunciadj894' => 'string',
        'coddependesemp' => 'string',
        'codc_uacad_seguro' => 'string',
        'tipo_norma' => 'string',
        'tipo_emite' => 'string',

        // Fechas
        'fec_altos' => 'date',
        'fec_endjp' => 'date',
        'fec_vtosf' => 'date',
        'fec_reasf' => 'date',
        'fec_defun' => 'date',
        'fecha_jubilacion' => 'date',
        'fecha_grado' => 'date',
        'fecha_permanencia' => 'date',
        'fechadjur894' => 'date',
        'fechadechere' => 'date',
        'fec_ingreso' => 'date',
        'fecha_recibo' => 'date',
        'fec_norma' => 'date',

        // Booleanos
        'sino_embargo' => 'boolean',
        'fuerza_reparto' => 'boolean',
    ];

    /**
     * Atributos que deben ser ocultados en arrays/JSON.
     */
    protected $hidden = [
        // Campos sensibles que no deben exponerse en APIs
    ];

    /**
     * Atributos que deben ser visibles en arrays/JSON.
     */
    protected $visible = [
        // Se pueden especificar campos específicos si es necesario
    ];

    // ========================================
    // SCOPES PARA CONSULTAS COMUNES
    // ========================================

    /**
     * Scope para filtrar por empleados activos (no fallecidos).
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereNull('fec_defun');
    }

    /**
     * Scope para filtrar por empleados jubilados.
     */
    public function scopeJubilados(Builder $query): Builder
    {
        return $query->where('sino_jubil', 'S');
    }

    /**
     * Scope para filtrar por empleados con embargo.
     */
    public function scopeConEmbargo(Builder $query): Builder
    {
        return $query->where('sino_embargo', true);
    }

    /**
     * Scope para filtrar por período de vigencia.
     */
    public function scopePorPeriodo(Builder $query, int $ano, int $mes): Builder
    {
        return $query->where('vig_otano', $ano)
            ->where('vig_otmes', $mes);
    }

    /**
     * Scope para filtrar por obra social.
     */
    public function scopePorObraSocial(Builder $query, string $codigoObraSocial): Builder
    {
        return $query->where('codc_obsoc', $codigoObraSocial);
    }

    /**
     * Scope para filtrar por dependencia.
     */
    public function scopePorDependencia(Builder $query, string $codigoDependencia): Builder
    {
        return $query->where('codc_uacad', $codigoDependencia);
    }

    // ========================================
    // ACCESSORS Y MUTATORS
    // ========================================

    /**
     * Accessor para obtener el estado de jubilación como booleano.
     */
    public function getEsJubiladoAttribute(): bool
    {
        return $this->sino_jubil === 'S';
    }

    /**
     * Accessor para obtener si tiene salario familiar en otro organismo.
     */
    public function getTieneSalarioFamiliarExternoAttribute(): bool
    {
        return $this->sino_otsal === 'S';
    }

    /**
     * Accessor para obtener la edad aproximada basada en fecha de ingreso.
     */
    public function getAntiguedadAttribute(): ?int
    {
        if (!$this->fec_ingreso) {
            return null;
        }

        try {
            return (int)$this->fec_ingreso->diffInYears(now());
        } catch (\Exception $e) {
            Log::warning('Error calculando antigüedad para legajo: ' . $this->nro_legaj, [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Accessor para verificar si el empleado está fallecido.
     */
    public function getEstaFallecidoAttribute(): bool
    {
        return $this->fec_defun !== null;
    }

    // ========================================
    // MÉTODOS DE UTILIDAD
    // ========================================

    /**
     * Verifica si el empleado tiene datos de obra social completos.
     */
    public function tieneObraSocialCompleta(): bool
    {
        return !empty($this->codc_obsoc) && !empty($this->nro_afili);
    }

    /**
     * Verifica si el empleado está en condiciones de jubilarse
     * (basado en fecha de jubilación o estado de jubilación).
     */
    public function puedeJubilarse(): bool
    {
        return $this->es_jubilado || $this->fecha_jubilacion !== null;
    }

    /**
     * Obtiene el período de vigencia como string formateado.
     */
    public function getPeriodoVigenciaFormateado(): string
    {
        if (!$this->vig_otano || !$this->vig_otmes) {
            return 'Sin período definido';
        }

        return \sprintf('%04d-%02d', $this->vig_otano, $this->vig_otmes);
    }

    /**
     * Verifica si los datos están vigentes para un período específico.
     */
    public function esVigenteEn(int $ano, int $mes): bool
    {
        return $this->vig_otano === $ano && $this->vig_otmes === $mes;
    }

    // ========================================
    // MÉTODOS ESTÁTICOS DE CONSULTA
    // ========================================

    /**
     * Busca empleados por número de legajo con manejo de errores.
     */
    public static function buscarPorLegajo(int $numeroLegajo): ?self
    {
        try {
            return static::where('nro_legaj', $numeroLegajo)->first();
        } catch (\Exception $e) {
            Log::error('Error buscando empleado por legajo', [
                'legajo' => $numeroLegajo,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtiene empleados por período con paginación.
     */
    public static function obtenerPorPeriodo(int $ano, int $mes, int $limite = 100): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        try {
            return static::porPeriodo($ano, $mes)
                ->orderBy('nro_legaj')
                ->paginate($limite);
        } catch (\Exception $e) {
            Log::error('Error obteniendo empleados por período', [
                'ano' => $ano,
                'mes' => $mes,
                'error' => $e->getMessage(),
            ]);

            // Retornar paginador vacío en caso de error
            return static::query()->paginate(0);
        }
    }

    /**
     * Obtiene estadísticas básicas de empleados por período.
     */
    public static function obtenerEstadisticasPorPeriodo(int $ano, int $mes): array
    {
        try {
            $query = static::porPeriodo($ano, $mes);

            return [
                'total_empleados' => $query->count(),
                'empleados_activos' => $query->activos()->count(),
                'empleados_jubilados' => $query->jubilados()->count(),
                'empleados_con_embargo' => $query->conEmbargo()->count(),
                'empleados_fallecidos' => $query->whereNotNull('fec_defun')->count(),
                'con_obra_social' => $query->whereNotNull('codc_obsoc')->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas por período', [
                'ano' => $ano,
                'mes' => $mes,
                'error' => $e->getMessage(),
            ]);

            return [
                'total_empleados' => 0,
                'empleados_activos' => 0,
                'empleados_jubilados' => 0,
                'empleados_con_embargo' => 0,
                'empleados_fallecidos' => 0,
                'con_obra_social' => 0,
            ];
        }
    }

    // ========================================
    // RELACIONES ELOQUENT
    // ========================================

    /**
     * Relación con la tabla de empleados (DH01)
     * Un empleado tiene un registro de otros datos.
     */
    public function empleado()
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    /**
     * Relación con tabla múltiple DH30 (nro_tab02).
     */
    public function tablaMultiple02()
    {
        return $this->belongsTo(Dh30::class, 'nro_tab02', 'nro_tabla');
    }

    /**
     * Relación con tabla múltiple DH30 (nro_tab08).
     */
    public function tablaMultiple08()
    {
        return $this->belongsTo(Dh30::class, 'nro_tab08', 'nro_tabla');
    }

    /**
     * Relación con tabla múltiple DH30 (nro_tab09).
     */
    public function tablaMultiple09()
    {
        return $this->belongsTo(Dh30::class, 'nro_tab09', 'nro_tabla');
    }

    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================

    /**
     * Valida la consistencia de los datos del modelo.
     */
    public function validarConsistencia(): array
    {
        $errores = [];

        try {
            // Validar fechas lógicas
            if ($this->fec_defun && $this->fec_ingreso && $this->fec_defun < $this->fec_ingreso) {
                $errores[] = 'La fecha de defunción no puede ser anterior a la fecha de ingreso';
            }

            if ($this->fecha_jubilacion && $this->fec_ingreso && $this->fecha_jubilacion < $this->fec_ingreso) {
                $errores[] = 'La fecha de jubilación no puede ser anterior a la fecha de ingreso';
            }

            // Validar coherencia de estado de jubilación
            if ($this->sino_jubil === 'S' && $this->fecha_jubilacion === null) {
                $errores[] = 'Si está marcado como jubilado, debe tener fecha de jubilación';
            }

            // Validar obra social
            if (!empty($this->codc_obsoc) && empty($this->nro_afili)) {
                $errores[] = 'Si tiene código de obra social, debe tener número de afiliado';
            }

            // Validar período de vigencia
            if ($this->vig_otmes !== null && ($this->vig_otmes < 1 || $this->vig_otmes > 12)) {
                $errores[] = 'El mes de vigencia debe estar entre 1 y 12';
            }

            if ($this->vig_otano !== null && $this->vig_otano < 1900) {
                $errores[] = 'El año de vigencia debe ser mayor a 1900';
            }
        } catch (\Exception $e) {
            Log::error('Error validando consistencia de DH09', [
                'legajo' => $this->nro_legaj,
                'error' => $e->getMessage(),
            ]);
            $errores[] = 'Error interno durante la validación';
        }

        return $errores;
    }

    /**
     * Verifica si el modelo tiene datos válidos para procesar.
     */
    public function esValidoParaProcesamiento(): bool
    {
        $errores = $this->validarConsistencia();
        return empty($errores);
    }

    // ========================================
    // MÉTODOS DE EXPORTACIÓN Y SERIALIZACIÓN
    // ========================================

    /**
     * Convierte el modelo a array con formato personalizado.
     */
    public function toArrayPersonalizado(): array
    {
        return [
            'legajo' => $this->nro_legaj,
            'periodo_vigencia' => $this->getPeriodoVigenciaFormateado(),
            'estado_civil' => $this->codc_estcv,
            'es_jubilado' => $this->es_jubilado,
            'tiene_embargo' => $this->sino_embargo,
            'obra_social' => [
                'codigo' => $this->codc_obsoc,
                'numero_afiliado' => $this->nro_afili,
                'fecha_alta' => $this->fec_altos?->format('Y-m-d'),
            ],
            'fechas_importantes' => [
                'ingreso' => $this->fec_ingreso?->format('Y-m-d'),
                'jubilacion' => $this->fecha_jubilacion?->format('Y-m-d'),
                'defuncion' => $this->fec_defun?->format('Y-m-d'),
            ],
            'dependencia' => [
                'codigo_region' => $this->codc_regio,
                'codigo_unidad' => $this->codc_uacad,
                'dependencia_desempeño' => $this->coddependesemp,
            ],
            'familia' => [
                'cantidad_a_cargo' => $this->cant_cargo,
                'conyuge_dependiente' => $this->conyugedependiente,
            ],
            'antiguedad_años' => $this->antiguedad,
            'esta_activo' => !$this->esta_fallecido,
        ];
    }

    // ========================================
    // MÉTODOS DE BÚSQUEDA AVANZADA
    // ========================================

    /**
     * Búsqueda avanzada con múltiples criterios.
     */
    public static function busquedaAvanzada(array $criterios): Builder
    {
        $query = static::query();

        try {
            // Filtro por legajo
            if (!empty($criterios['legajo'])) {
                $query->where('nro_legaj', $criterios['legajo']);
            }

            // Filtro por período
            if (!empty($criterios['ano']) && !empty($criterios['mes'])) {
                $query->porPeriodo($criterios['ano'], $criterios['mes']);
            }

            // Filtro por estado de jubilación
            if (isset($criterios['jubilado'])) {
                if ($criterios['jubilado']) {
                    $query->jubilados();
                } else {
                    $query->where('sino_jubil', '!=', 'S')->orWhereNull('sino_jubil');
                }
            }

            // Filtro por estado activo
            if (isset($criterios['activo'])) {
                if ($criterios['activo']) {
                    $query->activos();
                } else {
                    $query->whereNotNull('fec_defun');
                }
            }

            // Filtro por obra social
            if (!empty($criterios['obra_social'])) {
                $query->porObraSocial($criterios['obra_social']);
            }

            // Filtro por dependencia
            if (!empty($criterios['dependencia'])) {
                $query->porDependencia($criterios['dependencia']);
            }

            // Filtro por embargo
            if (isset($criterios['con_embargo'])) {
                $query->where('sino_embargo', $criterios['con_embargo']);
            }

            // Filtro por rango de fechas de ingreso
            if (!empty($criterios['fecha_ingreso_desde'])) {
                $query->where('fec_ingreso', '>=', $criterios['fecha_ingreso_desde']);
            }

            if (!empty($criterios['fecha_ingreso_hasta'])) {
                $query->where('fec_ingreso', '<=', $criterios['fecha_ingreso_hasta']);
            }
        } catch (\Exception $e) {
            Log::error('Error en búsqueda avanzada de DH09', [
                'criterios' => $criterios,
                'error' => $e->getMessage(),
            ]);
        }

        return $query;
    }

    // ========================================
    // MÉTODOS DE MANTENIMIENTO
    // ========================================

    /**
     * Limpia registros huérfanos o inconsistentes.
     */
    public static function limpiarRegistrosInconsistentes(): int
    {
        try {
            $registrosLimpiados = 0;

            // Buscar registros con datos inconsistentes
            $registrosInconsistentes = static::whereNotNull('fec_defun')
                ->where('fec_defun', '<', '1900-01-01')
                ->orWhere('vig_otmes', '>', 12)
                ->orWhere('vig_otmes', '<', 1);

            $registrosLimpiados = $registrosInconsistentes->count();

            if ($registrosLimpiados > 0) {
                Log::warning("Se encontraron {$registrosLimpiados} registros inconsistentes en DH09");

                // Aquí implementar la lógica de limpieza
                // Por seguridad, solo registramos el problema
            }

            return $registrosLimpiados;
        } catch (\Exception $e) {
            Log::error('Error limpiando registros inconsistentes de DH09', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Genera reporte de salud de los datos.
     */
    public static function generarReporteSalud(): array
    {
        try {
            return [
                'total_registros' => static::count(),
                'registros_con_periodo_valido' => static::whereNotNull('vig_otano')
                    ->whereNotNull('vig_otmes')
                    ->whereBetween('vig_otmes', [1, 12])
                    ->count(),
                'registros_con_obra_social' => static::whereNotNull('codc_obsoc')
                    ->whereNotNull('nro_afili')
                    ->count(),
                'empleados_jubilados' => static::jubilados()->count(),
                'empleados_fallecidos' => static::whereNotNull('fec_defun')->count(),
                'empleados_con_embargo' => static::conEmbargo()->count(),
                'registros_sin_fecha_ingreso' => static::whereNull('fec_ingreso')->count(),
                'ultima_actualizacion' => static::max('updated_at') ?? 'No disponible',
            ];
        } catch (\Exception $e) {
            Log::error('Error generando reporte de salud de DH09', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'No se pudo generar el reporte de salud',
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    // ========================================
    // EVENTOS DEL MODELO
    // ========================================

    /**
     * Configuración de eventos del modelo.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Evento antes de crear un registro
        static::creating(function ($model) {
            try {
                // Validar que el legajo no esté vacío
                if (empty($model->nro_legaj)) {
                    throw new \InvalidArgumentException('El número de legajo es requerido');
                }

                // Establecer valores por defecto si no están definidos
                if ($model->fuerza_reparto === null) {
                    $model->fuerza_reparto = false;
                }

                Log::info('Creando registro DH09 para legajo: ' . $model->nro_legaj);
            } catch (\Exception $e) {
                Log::error('Error en evento creating de DH09', [
                    'legajo' => $model->nro_legaj ?? 'null',
                    'error' => $e->getMessage(),
                ]);
                return false; // Cancela la creación
            }
        });

        // Evento antes de actualizar un registro
        static::updating(function ($model) {
            try {
                Log::info("Actualizando registro DH09 para legajo: {$model->nro_legaj}");

                // Validaciones adicionales si es necesario
                if ($model->isDirty('fec_defun') && !($model->fec_defun === null)) {
                    Log::warning('Se está registrando fecha de defunción para legajo: ' . $model->nro_legaj);
                }
            } catch (\Exception $e) {
                Log::error('Error en evento updating de DH09', [
                    'legajo' => $model->nro_legaj,
                    'error' => $e->getMessage(),
                ]);
                return false; // Cancela la actualización
            }
        });

        // Evento después de guardar (crear o actualizar)
        static::saved(function ($model): void {
            Log::info('Registro DH09 guardado exitosamente', [
                'legajo' => $model->nro_legaj,
                'periodo' => $model->getPeriodoVigenciaFormateado(),
            ]);
        });
    }
}
