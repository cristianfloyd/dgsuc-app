<?php

declare(strict_types=1);

namespace App\Models\Mapuche\Catalogo;

use App\Models\Mapuche\Dh05;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo para la tabla de Variantes de Licencias (dl02).
 *
 * @property int $nrovarlicencia Número de variante de licencia
 * @property int|null $nrodefiniclicencia Número de definición de licencia
 * @property string|null $codn_tipo_lic Nombre del tipo de licencia
 * @property bool|null $es_remunerada Licencia es remunerada? [S/N]
 * @property float|null $porcremuneracion Porcentaje de remuneración percibido
 * @property string|null $seaplicaa Aplica a personas o cargos
 * @property string|null $escalafon Escalafón válido para la licencia
 * @property bool|null $seapacualcaracter Aplica a cualquier carácter?[S/N]
 * @property bool|null $seapacualdedic Es para cualquier dedicación? [S/N]
 * @property string|null $sexo Sexo válido para la licencia
 * @property bool|null $control_fechas Existe control de fechas?[S/N]
 * @property string|null $unidad_tiempo Unidad de tiempo (Día, Mes, Año)
 * @property int|null $duracionenunidades Duración unidades de tiempo
 * @property string|null $tipo_dias Días corridos o hábiles
 * @property int|null $cantfragmentosmax Cantidad máxima de fragmentos
 * @property int|null $min_dias_fragmento Mínima cantidad de días por fragmento
 * @property int|null $max_dias_fragmento Máxima cantidad de días por fragmento
 * @property string|null $periodicidad Tipo de periodicidad
 * @property int|null $cantunidadesperiod Cantidad de unidades para la periodicidad
 * @property string|null $unidadtiempoantig Unidad de tiempo (Mes, Año)
 * @property int|null $antigdesdeenunidad Límite inferior de antigüedad para variante
 * @property int|null $antighastaenunidad Límite superior de antigüedad para variante
 * @property int|null $nroordenaplicacion Número de orden de aplicación
 * @property bool|null $computa_antiguedad Computa antigüedad remunerada? [S/N]
 * @property bool|null $computa_antig_ordi Computa antigüedad ordinaria? [S/N]
 * @property bool|null $es_absorcion Es una licencia por absorción
 * @property bool|null $es_maternidad Licencia por maternidad? [S/N]
 * @property bool|null $genera_vacante Genera una vacante? [S/N]
 * @property bool|null $libera_horas Libera horas para control por horas
 * @property int|null $libera_puntos Libera puntos para control por horas
 * @property string|null $observacion Observación para la variante
 * @property bool|null $subcontrol_fechas Subcontrol de fechas? [S/N]
 * @property int|null $subcantfragmentosmax Máxima cantidad de fragmentos subperíodo
 * @property int|null $submin_dias_fragmento Mín. cant. días c/fragmento - subperíodo
 * @property int|null $submax_dias_fragmento Máx. cant. días c/fragmento - subperíodo
 * @property string|null $subperiodicidad Tipo de periodicidad para subperiodo
 * @property int|null $subcantunidadesperiod Cant. de unidades para periodicidad - subperíodo
 * @property int|null $subduracionenunidades Duración de unidades del subperíodo
 * @property int|null $seapacualcateg Cualquier categoría? [S/N]
 * @property int|null $chkpresentismo Admite presentismo? [S/N]
 * @property int|null $chkaportalao Aporta meses trabajados? [S/N]
 * @property int|null $cantunidadesperiodo_sinusar
 */
class Dl02 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Indica si el modelo debe tener timestamps.
     */
    public $timestamps = false;

    /**
     * Indica si la clave primaria es auto-incrementable.
     */
    public $incrementing = false;

    /**
     * Nombre de la tabla en la base de datos.
     */
    protected $table = 'dl02';

    /**
     * Clave primaria de la tabla.
     */
    protected $primaryKey = 'nrovarlicencia';

    /**
     * Atributos que son asignables masivamente.
     */
    protected $fillable = [
        'nrovarlicencia',
        'nrodefiniclicencia',
        'codn_tipo_lic',
        'es_remunerada',
        'porcremuneracion',
        'seaplicaa',
        'escalafon',
        'seapacualcaracter',
        'seapacualdedic',
        'sexo',
        'control_fechas',
        'unidad_tiempo',
        'duracionenunidades',
        'tipo_dias',
        'cantfragmentosmax',
        'min_dias_fragmento',
        'max_dias_fragmento',
        'periodicidad',
        'cantunidadesperiod',
        'unidadtiempoantig',
        'antigdesdeenunidad',
        'antighastaenunidad',
        'nroordenaplicacion',
        'computa_antiguedad',
        'computa_antig_ordi',
        'es_absorcion',
        'es_maternidad',
        'genera_vacante',
        'libera_horas',
        'libera_puntos',
        'observacion',
        'subcontrol_fechas',
        'subcantfragmentosmax',
        'submin_dias_fragmento',
        'submax_dias_fragmento',
        'subperiodicidad',
        'subcantunidadesperiod',
        'subduracionenunidades',
        'seapacualcateg',
        'chkpresentismo',
        'chkaportalao',
        'cantunidadesperiodo_sinusar',
    ];

    /**
     * Relación con licencias (Dh05).
     */
    public function licencias(): HasMany
    {
        return $this->hasMany(Dh05::class, 'nrovarlicencia', 'nrovarlicencia');
    }

    // ================================
    // SCOPES DE CONSULTA
    // ================================

    /**
     * Scope para variantes remuneradas.
     */
    public function scopeRemuneradas(Builder $query): Builder
    {
        return $query->where('es_remunerada', true);
    }

    /**
     * Scope para variantes no remuneradas.
     */
    public function scopeNoRemuneradas(Builder $query): Builder
    {
        return $query->where('es_remunerada', false);
    }

    /**
     * Scope para licencias por maternidad.
     */
    public function scopeMaternidad(Builder $query): Builder
    {
        return $query->where('es_maternidad', true);
    }

    /**
     * Scope para licencias por absorción.
     */
    public function scopeAbsorcion(Builder $query): Builder
    {
        return $query->where('es_absorcion', true);
    }

    /**
     * Scope para licencias que generan vacante.
     */
    public function scopeGeneraVacante(Builder $query): Builder
    {
        return $query->where('genera_vacante', true);
    }

    /**
     * Scope para licencias que computan antigüedad.
     */
    public function scopeComputaAntiguedad(Builder $query): Builder
    {
        return $query->where('computa_antiguedad', true);
    }

    /**
     * Scope para filtrar por escalafón.
     */
    public function scopePorEscalafon(Builder $query, string $escalafon): Builder
    {
        return $query->where('escalafon', $escalafon);
    }

    /**
     * Scope para filtrar por sexo.
     */
    public function scopePorSexo(Builder $query, string $sexo): Builder
    {
        return $query->where('sexo', $sexo);
    }

    /**
     * Scope para licencias con control de fechas.
     */
    public function scopeConControlFechas(Builder $query): Builder
    {
        return $query->where('control_fechas', true);
    }

    /**
     * Scope para filtrar por tipo de días.
     */
    public function scopePorTipoDias(Builder $query, string $tipoDias): Builder
    {
        return $query->where('tipo_dias', $tipoDias);
    }

    /**
     * Scope para ordenar por número de orden de aplicación.
     */
    public function scopeOrdenadoPorAplicacion(Builder $query): Builder
    {
        return $query->orderBy('nroordenaplicacion');
    }

    // ================================
    // MÉTODOS DE NEGOCIO
    // ================================

    /**
     * Verifica si la licencia es remunerada con porcentaje específico.
     */
    public function esRemuneradaConPorcentaje(float $porcentaje = 100.0): bool
    {
        return $this->es_remunerada &&
               $this->porcremuneracion !== null &&
               $this->porcremuneracion >= $porcentaje;
    }

    /**
     * Verifica si la licencia es completamente no remunerada.
     */
    public function esCompletamenteNoRemunerada(): bool
    {
        return !$this->es_remunerada ||
               ($this->porcremuneracion !== null && $this->porcremuneracion == 0);
    }

    /**
     * Verifica si aplica para cualquier carácter.
     */
    public function aplicaACualquierCaracter(): bool
    {
        return (bool) $this->seapacualcaracter;
    }

    /**
     * Verifica si aplica para cualquier dedicación.
     */
    public function aplicaACualquierDedicacion(): bool
    {
        return (bool) $this->seapacualdedic;
    }

    /**
     * Obtiene la descripción completa de la variante.
     */
    public function getDescripcionCompleta(): string
    {
        $descripcion = "Variante {$this->nrovarlicencia}";

        if ($this->observacion) {
            $descripcion .= " - {$this->observacion}";
        }

        if ($this->es_remunerada) {
            $porcentaje = $this->porcremuneracion ?? 100;
            $descripcion .= " (Remunerada {$porcentaje}%)";
        } else {
            $descripcion .= ' (No remunerada)';
        }

        if ($this->es_maternidad) {
            $descripcion .= ' [Maternidad]';
        }

        return $descripcion;
    }

    /**
     * Verifica si la variante es aplicable según los criterios.
     */
    public function esAplicable(array $criterios = []): bool
    {
        // Verificar sexo si está especificado
        if (isset($criterios['sexo']) && $this->sexo && $this->sexo !== $criterios['sexo']) {
            return false;
        }

        // Verificar escalafón si está especificado
        return !(isset($criterios['escalafon']) && $this->escalafon && $this->escalafon !== $criterios['escalafon']);



        // Agregar más validaciones según sea necesario
    }

    /**
     * Calcula el importe a percibir basado en el sueldo base.
     */
    public function calcularImporteRemuneracion(float $sueldoBase): float
    {
        if (!$this->es_remunerada || $this->porcremuneracion === null) {
            return 0.0;
        }

        return $sueldoBase * ($this->porcremuneracion / 100);
    }

    /**
     * Casteos de atributos.
     */
    protected function casts(): array
    {
        return [
            'nrovarlicencia' => 'integer',
            'nrodefiniclicencia' => 'integer',
            'codn_tipo_lic' => 'string',
            'es_remunerada' => 'boolean',
            'porcremuneracion' => 'decimal:2',
            'seaplicaa' => 'string',
            'escalafon' => 'string',
            'seapacualcaracter' => 'boolean',
            'seapacualdedic' => 'boolean',
            'sexo' => 'string',
            'control_fechas' => 'boolean',
            'unidad_tiempo' => 'string',
            'duracionenunidades' => 'integer',
            'tipo_dias' => 'string',
            'cantfragmentosmax' => 'integer',
            'min_dias_fragmento' => 'integer',
            'max_dias_fragmento' => 'integer',
            'periodicidad' => 'string',
            'cantunidadesperiod' => 'integer',
            'unidadtiempoantig' => 'string',
            'antigdesdeenunidad' => 'integer',
            'antighastaenunidad' => 'integer',
            'nroordenaplicacion' => 'integer',
            'computa_antiguedad' => 'boolean',
            'computa_antig_ordi' => 'boolean',
            'es_absorcion' => 'boolean',
            'es_maternidad' => 'boolean',
            'genera_vacante' => 'boolean',
            'libera_horas' => 'boolean',
            'libera_puntos' => 'integer',
            'observacion' => 'string',
            'subcontrol_fechas' => 'boolean',
            'subcantfragmentosmax' => 'integer',
            'submin_dias_fragmento' => 'integer',
            'submax_dias_fragmento' => 'integer',
            'subperiodicidad' => 'string',
            'subcantunidadesperiod' => 'integer',
            'subduracionenunidades' => 'integer',
            'seapacualcateg' => 'integer',
            'chkpresentismo' => 'integer',
            'chkaportalao' => 'integer',
            'cantunidadesperiodo_sinusar' => 'integer',
        ];
    }
}
