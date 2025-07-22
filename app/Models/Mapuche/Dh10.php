<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Models\Dh03;
use App\Traits\MapucheConnectionTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para la tabla de Brutos Acumulados para SAC (dh10).
 *
 * @property int $nro_cargo Cargo del Empleado
 * @property int $vcl_cargo Vínculo Cargo Orígen
 *
 * Importes Bruto Acumulados para SAC (12 meses):
 * @property float|null $imp_bruto_1 Importe Bruto Acumulado para SAC - Enero
 * @property float|null $imp_bruto_2 Importe Bruto Acumulado para SAC - Febrero
 * @property float|null $imp_bruto_3 Importe Bruto Acumulado para SAC - Marzo
 * @property float|null $imp_bruto_4 Importe Bruto Acumulado para SAC - Abril
 * @property float|null $imp_bruto_5 Importe Bruto Acumulado para SAC - Mayo
 * @property float|null $imp_bruto_6 Importe Bruto Acumulado para SAC - Junio
 * @property float|null $imp_bruto_7 Importe Bruto Acumulado para SAC - Julio
 * @property float|null $imp_bruto_8 Importe Bruto Acumulado para SAC - Agosto
 * @property float|null $imp_bruto_9 Importe Bruto Acumulado para SAC - Septiembre
 * @property float|null $imp_bruto_10 Importe Bruto Acumulado para SAC - Octubre
 * @property float|null $imp_bruto_11 Importe Bruto Acumulado para SAC - Noviembre
 * @property float|null $imp_bruto_12 Importe Bruto Acumulado para SAC - Diciembre
 *
 * Importes Retroactivos Acumulados Periodo Corriente (12 meses):
 * @property float|null $importes_retro_1 Importes Retroactivos Acum. Periodo Cte. - Enero
 * @property float|null $importes_retro_2 Importes Retroactivos Acum. Periodo Cte. - Febrero
 * @property float|null $importes_retro_3 Importes Retroactivos Acum. Periodo Cte. - Marzo
 * @property float|null $importes_retro_4 Importes Retroactivos Acum. Periodo Cte. - Abril
 * @property float|null $importes_retro_5 Importes Retroactivos Acum. Periodo Cte. - Mayo
 * @property float|null $importes_retro_6 Importes Retroactivos Acum. Periodo Cte. - Junio
 * @property float|null $importes_retro_7 Importes Retroactivos Acum. Periodo Cte. - Julio
 * @property float|null $importes_retro_8 Importes Retroactivos Acum. Periodo Cte. - Agosto
 * @property float|null $importes_retro_9 Importes Retroactivos Acum. Periodo Cte. - Septiembre
 * @property float|null $importes_retro_10 Importes Retroactivos Acum. Periodo Cte. - Octubre
 * @property float|null $importes_retro_11 Importes Retroactivos Acum. Periodo Cte. - Noviembre
 * @property float|null $importes_retro_12 Importes Retroactivos Acum. Periodo Cte. - Diciembre
 *
 * Importes Brutos Haber Promedio (18 períodos):
 * @property float|null $impbrhbrprom_1 Importes Brutos Haber Promedio - Período 1
 * @property float|null $impbrhbrprom_2 Importes Brutos Haber Promedio - Período 2
 * @property float|null $impbrhbrprom_3 Importes Brutos Haber Promedio - Período 3
 * @property float|null $impbrhbrprom_4 Importes Brutos Haber Promedio - Período 4
 * @property float|null $impbrhbrprom_5 Importes Brutos Haber Promedio - Período 5
 * @property float|null $impbrhbrprom_6 Importes Brutos Haber Promedio - Período 6
 * @property float|null $impbrhbrprom_7 Importes Brutos Haber Promedio - Período 7
 * @property float|null $impbrhbrprom_8 Importes Brutos Haber Promedio - Período 8
 * @property float|null $impbrhbrprom_9 Importes Brutos Haber Promedio - Período 9
 * @property float|null $impbrhbrprom_10 Importes Brutos Haber Promedio - Período 10
 * @property float|null $impbrhbrprom_11 Importes Brutos Haber Promedio - Período 11
 * @property float|null $impbrhbrprom_12 Importes Brutos Haber Promedio - Período 12
 * @property float|null $impbrhbrprom_13 Importes Brutos Haber Promedio - Período 13
 * @property float|null $impbrhbrprom_14 Importes Brutos Haber Promedio - Período 14
 * @property float|null $impbrhbrprom_15 Importes Brutos Haber Promedio - Período 15
 * @property float|null $impbrhbrprom_16 Importes Brutos Haber Promedio - Período 16
 * @property float|null $impbrhbrprom_17 Importes Brutos Haber Promedio - Período 17
 * @property float|null $impbrhbrprom_18 Importes Brutos Haber Promedio - Período 18
 *
 * Retro Brutos Haber Promedio Acumulado Periodo Corriente (18 períodos):
 * @property float|null $retroimpbrhbrpr_1 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 1
 * @property float|null $retroimpbrhbrpr_2 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 2
 * @property float|null $retroimpbrhbrpr_3 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 3
 * @property float|null $retroimpbrhbrpr_4 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 4
 * @property float|null $retroimpbrhbrpr_5 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 5
 * @property float|null $retroimpbrhbrpr_6 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 6
 * @property float|null $retroimpbrhbrpr_7 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 7
 * @property float|null $retroimpbrhbrpr_8 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 8
 * @property float|null $retroimpbrhbrpr_9 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 9
 * @property float|null $retroimpbrhbrpr_10 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 10
 * @property float|null $retroimpbrhbrpr_11 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 11
 * @property float|null $retroimpbrhbrpr_12 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 12
 * @property float|null $retroimpbrhbrpr_13 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 13
 * @property float|null $retroimpbrhbrpr_14 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 14
 * @property float|null $retroimpbrhbrpr_15 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 15
 * @property float|null $retroimpbrhbrpr_16 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 16
 * @property float|null $retroimpbrhbrpr_17 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 17
 * @property float|null $retroimpbrhbrpr_18 Retro Brutos Haber Prom.Acum.Periodo Cte - Período 18
 *
 * Atributos computados:
 * @property-read array $importes_brutos_mensuales Array con importes brutos indexados por mes
 * @property-read array $importes_retroactivos_mensuales Array con importes retroactivos indexados por mes
 * @property-read array $importes_haber_promedio Array con importes haber promedio indexados por período
 * @property-read array $retro_haber_promedio Array con retro haber promedio indexados por período
 * @property-read float $total_importes_brutos Total de importes brutos del año
 * @property-read float $total_importes_retroactivos Total de importes retroactivos del año
 * @property-read bool $tiene_vinculo Indica si el cargo tiene vínculo
 * @property-read float $promedio_mensual_brutos Promedio mensual de importes brutos
 *
 * Relaciones:
 * @property-read Dh03|null $cargo Relación con el cargo
 * @property-read Dh10|null $cargoVinculado Relación con el cargo vinculado
 *
 * Scopes:
 *
 * @method static Builder|self conVinculo() Cargos que tienen vínculos
 * @method static Builder|self sinVinculo() Cargos sin vínculos
 * @method static Builder|self conImportesMes(int $mes) Cargos con importes en un mes específico
 * @method static Builder|self porPeriodo(Carbon $fechaInicio, Carbon $fechaFin) Cargos activos en un período
 */
class Dh10 extends Model
{
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
    protected $table = 'dh10';

    /**
     * Clave primaria de la tabla.
     */
    protected $primaryKey = 'nro_cargo';

    /**
     * Atributos que son asignables masivamente.
     */
    protected $fillable = [
        'nro_cargo',
        'vcl_cargo',
        // Importes brutos mensuales (12 meses)
        'imp_bruto_1', 'imp_bruto_2', 'imp_bruto_3', 'imp_bruto_4',
        'imp_bruto_5', 'imp_bruto_6', 'imp_bruto_7', 'imp_bruto_8',
        'imp_bruto_9', 'imp_bruto_10', 'imp_bruto_11', 'imp_bruto_12',
        // Importes retroactivos mensuales (12 meses)
        'importes_retro_1', 'importes_retro_2', 'importes_retro_3', 'importes_retro_4',
        'importes_retro_5', 'importes_retro_6', 'importes_retro_7', 'importes_retro_8',
        'importes_retro_9', 'importes_retro_10', 'importes_retro_11', 'importes_retro_12',
        // Importes brutos haber promedio (18 períodos)
        'impbrhbrprom_1', 'impbrhbrprom_2', 'impbrhbrprom_3', 'impbrhbrprom_4',
        'impbrhbrprom_5', 'impbrhbrprom_6', 'impbrhbrprom_7', 'impbrhbrprom_8',
        'impbrhbrprom_9', 'impbrhbrprom_10', 'impbrhbrprom_11', 'impbrhbrprom_12',
        'impbrhbrprom_13', 'impbrhbrprom_14', 'impbrhbrprom_15', 'impbrhbrprom_16',
        'impbrhbrprom_17', 'impbrhbrprom_18',
        // Retro brutos haber promedio (18 períodos)
        'retroimpbrhbrpr_1', 'retroimpbrhbrpr_2', 'retroimpbrhbrpr_3', 'retroimpbrhbrpr_4',
        'retroimpbrhbrpr_5', 'retroimpbrhbrpr_6', 'retroimpbrhbrpr_7', 'retroimpbrhbrpr_8',
        'retroimpbrhbrpr_9', 'retroimpbrhbrpr_10', 'retroimpbrhbrpr_11', 'retroimpbrhbrpr_12',
        'retroimpbrhbrpr_13', 'retroimpbrhbrpr_14', 'retroimpbrhbrpr_15', 'retroimpbrhbrpr_16',
        'retroimpbrhbrpr_17', 'retroimpbrhbrpr_18',
    ];

    /**
     * Casteos de atributos.
     */
    protected $casts = [
        'nro_cargo' => 'integer',
        'vcl_cargo' => 'integer',
        // Importes brutos mensuales
        'imp_bruto_1' => 'decimal:2', 'imp_bruto_2' => 'decimal:2', 'imp_bruto_3' => 'decimal:2',
        'imp_bruto_4' => 'decimal:2', 'imp_bruto_5' => 'decimal:2', 'imp_bruto_6' => 'decimal:2',
        'imp_bruto_7' => 'decimal:2', 'imp_bruto_8' => 'decimal:2', 'imp_bruto_9' => 'decimal:2',
        'imp_bruto_10' => 'decimal:2', 'imp_bruto_11' => 'decimal:2', 'imp_bruto_12' => 'decimal:2',
        // Importes retroactivos mensuales
        'importes_retro_1' => 'decimal:2', 'importes_retro_2' => 'decimal:2', 'importes_retro_3' => 'decimal:2',
        'importes_retro_4' => 'decimal:2', 'importes_retro_5' => 'decimal:2', 'importes_retro_6' => 'decimal:2',
        'importes_retro_7' => 'decimal:2', 'importes_retro_8' => 'decimal:2', 'importes_retro_9' => 'decimal:2',
        'importes_retro_10' => 'decimal:2', 'importes_retro_11' => 'decimal:2', 'importes_retro_12' => 'decimal:2',
        // Importes haber promedio (18 períodos)
        'impbrhbrprom_1' => 'decimal:2', 'impbrhbrprom_2' => 'decimal:2', 'impbrhbrprom_3' => 'decimal:2',
        'impbrhbrprom_4' => 'decimal:2', 'impbrhbrprom_5' => 'decimal:2', 'impbrhbrprom_6' => 'decimal:2',
        'impbrhbrprom_7' => 'decimal:2', 'impbrhbrprom_8' => 'decimal:2', 'impbrhbrprom_9' => 'decimal:2',
        'impbrhbrprom_10' => 'decimal:2', 'impbrhbrprom_11' => 'decimal:2', 'impbrhbrprom_12' => 'decimal:2',
        'impbrhbrprom_13' => 'decimal:2', 'impbrhbrprom_14' => 'decimal:2', 'impbrhbrprom_15' => 'decimal:2',
        'impbrhbrprom_16' => 'decimal:2', 'impbrhbrprom_17' => 'decimal:2', 'impbrhbrprom_18' => 'decimal:2',
        // Retro haber promedio (18 períodos)
        'retroimpbrhbrpr_1' => 'decimal:2', 'retroimpbrhbrpr_2' => 'decimal:2', 'retroimpbrhbrpr_3' => 'decimal:2',
        'retroimpbrhbrpr_4' => 'decimal:2', 'retroimpbrhbrpr_5' => 'decimal:2', 'retroimpbrhbrpr_6' => 'decimal:2',
        'retroimpbrhbrpr_7' => 'decimal:2', 'retroimpbrhbrpr_8' => 'decimal:2', 'retroimpbrhbrpr_9' => 'decimal:2',
        'retroimpbrhbrpr_10' => 'decimal:2', 'retroimpbrhbrpr_11' => 'decimal:2', 'retroimpbrhbrpr_12' => 'decimal:2',
        'retroimpbrhbrpr_13' => 'decimal:2', 'retroimpbrhbrpr_14' => 'decimal:2', 'retroimpbrhbrpr_15' => 'decimal:2',
        'retroimpbrhbrpr_16' => 'decimal:2', 'retroimpbrhbrpr_17' => 'decimal:2', 'retroimpbrhbrpr_18' => 'decimal:2',
    ];

    /**
     * Atributos que deben agregarse al array/JSON del modelo.
     */
    protected $appends = [
        'importes_brutos_mensuales',
        'importes_retroactivos_mensuales',
        'importes_haber_promedio',
        'retro_haber_promedio',
        'total_importes_brutos',
        'total_importes_retroactivos',
        'tiene_vinculo',
        'promedio_mensual_brutos',
    ];

    // ==============================================
    // RELACIONES
    // ==============================================

    /**
     * Relación con el modelo Dh03 (cargo).
     */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Dh03::class, 'nro_cargo', 'nro_cargo');
    }

    /**
     * Relación con cargo vinculado (auto-relación).
     */
    public function cargoVinculado(): BelongsTo
    {
        return $this->belongsTo(self::class, 'vcl_cargo', 'nro_cargo');
    }

    // ==============================================
    // SCOPES
    // ==============================================

    /**
     * Scope para cargos con vínculos.
     */
    public function scopeConVinculo(Builder $query): Builder
    {
        return $query->whereColumn('nro_cargo', '!=', 'vcl_cargo');
    }

    /**
     * Scope para cargos sin vínculos.
     */
    public function scopeSinVinculo(Builder $query): Builder
    {
        return $query->whereColumn('nro_cargo', '=', 'vcl_cargo');
    }

    /**
     * Scope para cargos con importes en un mes específico.
     */
    public function scopeConImportesMes(Builder $query, int $mes): Builder
    {
        if ($mes < 1 || $mes > 12) {
            throw new \InvalidArgumentException("El mes debe estar entre 1 y 12. Recibido: {$mes}");
        }

        return $query->where("imp_bruto_{$mes}", '>', 0);
    }

    /**
     * Scope para filtrar por período fiscal.
     */
    public function scopePorPeriodoFiscal(Builder $query, int $anio, ?int $semestre = null): Builder
    {
        if ($semestre === 1) {
            // Primer semestre: enero a junio
            $query->where(function ($q): void {
                for ($mes = 1; $mes <= 6; $mes++) {
                    $q->orWhere("imp_bruto_{$mes}", '>', 0);
                }
            });
        } elseif ($semestre === 2) {
            // Segundo semestre: julio a diciembre
            $query->where(function ($q): void {
                for ($mes = 7; $mes <= 12; $mes++) {
                    $q->orWhere("imp_bruto_{$mes}", '>', 0);
                }
            });
        }

        return $query;
    }

    // ==============================================
    // MÉTODOS DE UTILIDAD
    // ==============================================

    /**
     * Obtiene el importe bruto de un mes específico.
     */
    public function getImporteBrutoMes(int $mes): float
    {
        if ($mes < 1 || $mes > 12) {
            throw new \InvalidArgumentException("El mes debe estar entre 1 y 12. Recibido: {$mes}");
        }

        return $this->{"imp_bruto_{$mes}"} ?? 0;
    }

    /**
     * Establece el importe bruto de un mes específico.
     */
    public function setImporteBrutoMes(int $mes, float $importe): self
    {
        if ($mes < 1 || $mes > 12) {
            throw new \InvalidArgumentException("El mes debe estar entre 1 y 12. Recibido: {$mes}");
        }

        $this->{"imp_bruto_{$mes}"} = $importe;
        return $this;
    }

    /**
     * Obtiene el importe retroactivo de un mes específico.
     */
    public function getImporteRetroactivoMes(int $mes): float
    {
        if ($mes < 1 || $mes > 12) {
            throw new \InvalidArgumentException("El mes debe estar entre 1 y 12. Recibido: {$mes}");
        }

        return $this->{"importes_retro_{$mes}"} ?? 0;
    }

    /**
     * Obtiene el mayor importe bruto del semestre.
     */
    public function getMayorImporteSemestre(int $semestre): array
    {
        $meses = $semestre === 1 ? range(1, 6) : range(7, 12);
        $mayor = ['mes' => 0, 'importe' => 0];

        foreach ($meses as $mes) {
            $importe = $this->getImporteBrutoMes($mes);
            if ($importe > $mayor['importe']) {
                $mayor = ['mes' => $mes, 'importe' => $importe];
            }
        }

        return $mayor;
    }

    /**
     * Inicializa todos los campos de importes en cero para un cargo nuevo.
     */
    public function inicializarImportes(): self
    {
        // Importes brutos mensuales
        for ($i = 1; $i <= 12; $i++) {
            $this->{"imp_bruto_{$i}"} = 0;
            $this->{"importes_retro_{$i}"} = 0;
        }

        // Importes haber promedio y retro haber promedio
        for ($i = 1; $i <= 18; $i++) {
            $this->{"impbrhbrprom_{$i}"} = 0;
            $this->{"retroimpbrhbrpr_{$i}"} = 0;
        }

        return $this;
    }

    /**
     * Verifica si el cargo tiene importes en un rango de meses.
     */
    public function tieneImportesEnRango(int $mesInicio, int $mesFin): bool
    {
        for ($mes = $mesInicio; $mes <= $mesFin; $mes++) {
            if ($this->getImporteBrutoMes($mes) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtiene un resumen estadístico de los importes.
     */
    public function getResumenEstadistico(): array
    {
        $importes = $this->importes_brutos_mensuales;
        $importesConValor = array_filter($importes, fn ($importe) => $importe > 0);

        return [
            'total_anual' => $this->total_importes_brutos,
            'promedio_mensual' => $this->promedio_mensual_brutos,
            'meses_con_importes' => \count($importesConValor),
            'mes_mayor_importe' => $this->getMayorImporteSemestre(1)['importe'] > $this->getMayorImporteSemestre(2)['importe']
                ? $this->getMayorImporteSemestre(1)
                : $this->getMayorImporteSemestre(2),
            'tiene_vinculo' => $this->tiene_vinculo,
            'primer_semestre_total' => array_sum(\array_slice($importes, 0, 6)),
            'segundo_semestre_total' => array_sum(\array_slice($importes, 6, 6)),
        ];
    }

    // ==============================================
    // ACCESSORS (ATRIBUTOS COMPUTADOS)
    // ==============================================

    /**
     * Obtiene los importes brutos mensuales como array indexado.
     */
    protected function importesBrutosMensuales(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                1 => $this->imp_bruto_1 ?? 0,
                2 => $this->imp_bruto_2 ?? 0,
                3 => $this->imp_bruto_3 ?? 0,
                4 => $this->imp_bruto_4 ?? 0,
                5 => $this->imp_bruto_5 ?? 0,
                6 => $this->imp_bruto_6 ?? 0,
                7 => $this->imp_bruto_7 ?? 0,
                8 => $this->imp_bruto_8 ?? 0,
                9 => $this->imp_bruto_9 ?? 0,
                10 => $this->imp_bruto_10 ?? 0,
                11 => $this->imp_bruto_11 ?? 0,
                12 => $this->imp_bruto_12 ?? 0,
            ],
        );
    }

    /**
     * Obtiene los importes retroactivos mensuales como array indexado.
     */
    protected function importesRetroactivosMensuales(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                1 => $this->importes_retro_1 ?? 0,
                2 => $this->importes_retro_2 ?? 0,
                3 => $this->importes_retro_3 ?? 0,
                4 => $this->importes_retro_4 ?? 0,
                5 => $this->importes_retro_5 ?? 0,
                6 => $this->importes_retro_6 ?? 0,
                7 => $this->importes_retro_7 ?? 0,
                8 => $this->importes_retro_8 ?? 0,
                9 => $this->importes_retro_9 ?? 0,
                10 => $this->importes_retro_10 ?? 0,
                11 => $this->importes_retro_11 ?? 0,
                12 => $this->importes_retro_12 ?? 0,
            ],
        );
    }

    /**
     * Obtiene los importes haber promedio como array indexado.
     */
    protected function importesHaberPromedio(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                1 => $this->impbrhbrprom_1 ?? 0, 2 => $this->impbrhbrprom_2 ?? 0,
                3 => $this->impbrhbrprom_3 ?? 0, 4 => $this->impbrhbrprom_4 ?? 0,
                5 => $this->impbrhbrprom_5 ?? 0, 6 => $this->impbrhbrprom_6 ?? 0,
                7 => $this->impbrhbrprom_7 ?? 0, 8 => $this->impbrhbrprom_8 ?? 0,
                9 => $this->impbrhbrprom_9 ?? 0, 10 => $this->impbrhbrprom_10 ?? 0,
                11 => $this->impbrhbrprom_11 ?? 0, 12 => $this->impbrhbrprom_12 ?? 0,
                13 => $this->impbrhbrprom_13 ?? 0, 14 => $this->impbrhbrprom_14 ?? 0,
                15 => $this->impbrhbrprom_15 ?? 0, 16 => $this->impbrhbrprom_16 ?? 0,
                17 => $this->impbrhbrprom_17 ?? 0, 18 => $this->impbrhbrprom_18 ?? 0,
            ],
        );
    }

    /**
     * Obtiene los retro haber promedio como array indexado.
     */
    protected function retroHaberPromedio(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                1 => $this->retroimpbrhbrpr_1 ?? 0, 2 => $this->retroimpbrhbrpr_2 ?? 0,
                3 => $this->retroimpbrhbrpr_3 ?? 0, 4 => $this->retroimpbrhbrpr_4 ?? 0,
                5 => $this->retroimpbrhbrpr_5 ?? 0, 6 => $this->retroimpbrhbrpr_6 ?? 0,
                7 => $this->retroimpbrhbrpr_7 ?? 0, 8 => $this->retroimpbrhbrpr_8 ?? 0,
                9 => $this->retroimpbrhbrpr_9 ?? 0, 10 => $this->retroimpbrhbrpr_10 ?? 0,
                11 => $this->retroimpbrhbrpr_11 ?? 0, 12 => $this->retroimpbrhbrpr_12 ?? 0,
                13 => $this->retroimpbrhbrpr_13 ?? 0, 14 => $this->retroimpbrhbrpr_14 ?? 0,
                15 => $this->retroimpbrhbrpr_15 ?? 0, 16 => $this->retroimpbrhbrpr_16 ?? 0,
                17 => $this->retroimpbrhbrpr_17 ?? 0, 18 => $this->retroimpbrhbrpr_18 ?? 0,
            ],
        );
    }

    /**
     * Calcula el total de importes brutos del año.
     */
    protected function totalImportesBrutos(): Attribute
    {
        return Attribute::make(
            get: fn () => array_sum($this->importes_brutos_mensuales),
        );
    }

    /**
     * Calcula el total de importes retroactivos del año.
     */
    protected function totalImportesRetroactivos(): Attribute
    {
        return Attribute::make(
            get: fn () => array_sum($this->importes_retroactivos_mensuales),
        );
    }

    /**
     * Indica si el cargo tiene vínculo.
     */
    protected function tieneVinculo(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nro_cargo !== $this->vcl_cargo,
        );
    }

    /**
     * Calcula el promedio mensual de importes brutos.
     */
    protected function promedioMensualBrutos(): Attribute
    {
        return Attribute::make(
            get: function () {
                $importes = array_filter($this->importes_brutos_mensuales, fn ($importe) => $importe > 0);
                return \count($importes) > 0 ? array_sum($importes) / \count($importes) : 0;
            },
        );
    }
}
