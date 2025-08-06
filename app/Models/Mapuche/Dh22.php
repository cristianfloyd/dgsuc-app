<?php

namespace App\Models\Mapuche;

use App\Models\EstadoLiquidacionModel;
use App\Services\EncodingService;
use App\Traits\Mapuche\EncodingTrait;
use App\Traits\MapucheConnectionTrait;
use App\ValueObjects\NroLiqui;
use App\ValueObjects\PeriodoFiscal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Log;

/**
 * Modelo Eloquent para la tabla mapuche.dh22.
 *
 * Esta clase representa la tabla de liquidaciones en el sistema.
 *
 * @method static select(string $string)
 * @method static orderBy(string $string, string $string1)
 * @method static where(string $string, int $nroLiqui)
 */
class Dh22 extends Model
{
    use MapucheConnectionTrait;
    use HasFactory;
    use EncodingTrait;

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indica el nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'dh22';

    protected $schema = 'mapuche';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'nro_liqui';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     *
     * @phpstan-ignore property.phpDocType
     */
    protected $fillable = [
        'per_liano', 'per_limes', 'desc_liqui', 'fec_ultap', 'per_anoap', 'per_mesap',
        'desc_lugap', 'fec_emisi', 'desc_emisi', 'vig_emano', 'vig_emmes', 'vig_caano',
        'vig_cames', 'vig_coano', 'vig_comes', 'codn_econo', 'sino_cerra', 'sino_aguin',
        'sino_reten', 'sino_genimp', 'nrovalorpago', 'finimpresrecibos', 'id_tipo_liqui',
    ];

    /**
     * Atributos que deben agregarse automáticamente al array/JSON del modelo.
     *
     * @var list<string>
     */
    protected $appends = ['descripcion_completa'];

    /**
     * Campos que requieren conversión de codificación.
     */
    protected $encodedFields = [
        'desc_liqui',
    ];

    /**
     * Obtiene el tipo de liquidación asociado.
     *
     * @return BelongsTo<Dh22Tipo, $this>
     */
    public function tipoLiquidacion(): BelongsTo
    {
        return $this->belongsTo(Dh22Tipo::class, 'id_tipo_liqui', 'id');
    }

    /**
     * Obtiene el estado de liquidación asociado.
     *
     * @return BelongsTo<EstadoLiquidacionModel, $this>
     */
    public function estadoLiquidacion(): BelongsTo
    {
        return $this->belongsTo(EstadoLiquidacionModel::class, 'sino_cerra', 'cod_estado_liquidacion');
    }

    /**
     * Prepara una consulta para obtener liquidaciones con información básica para un widget.
     *
     * Selecciona el número de liquidación, periodo fiscal formateado y descripción,
     * ordenados por número de liquidación en orden descendente.
     *
     * @return Builder Consulta de liquidaciones preparada para ser ejecutada
     */
    public static function getLiquidacionesForWidget($periodoFiscal = null): Builder
    {
        return self::query()
            ->select('nro_liqui')
            ->selectRaw("CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) as periodo_fiscal")
            ->addSelect('desc_liqui')
            ->orderByDesc('nro_liqui');
    }

    /**
     * Obtiene la descripción de una liquidación específica por su número.
     *
     * @param int|NroLiqui $nro_liqui Número de liquidación o instancia de NroLiqui
     *
     * @return string Descripción de la liquidación
     */
    public static function getDescripcionLiquidacion($nro_liqui): string
    {
        $nroLiquiValue = $nro_liqui instanceof NroLiqui ? $nro_liqui->value() : $nro_liqui;

        return static::select('desc_liqui')
            ->where('nro_liqui', $nroLiquiValue)
            ->first()
            ->desc_liqui;
    }

    /**
     * Obtiene la ultima liquidación abierta.
     */
    public static function getUltimaLiquidacionAbierta(): self
    {
        return static::query()
            ->where('sino_cerra', '!=', 'C')
            ->orderBy('nro_liqui', 'desc')
            ->first();
    }

    /**
     * Obtiene las últimas tres liquidaciones definitivas.
     */
    public static function getUltimasTresLiquidacionesDefinitivas(): Collection
    {
        return static::query()
            ->definitiva()
            ->orderBy('nro_liqui', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Obtiene el último nro_liqui de la tabla dh22.
     */
    public static function getLastIdLiquidacion(): int
    {
        try {
            // Realiza la consulta utilizando Eloquent y DB Facade
            $lastId = self::orderBy('nro_liqui', 'desc')
                ->value('nro_liqui');

            // Retorna el último nro_liqui o 0 si no se encuentra ninguno
            return $lastId ?? 0;
        } catch (\Exception $e) {
            // Manejo de excepciones
            // Puedes registrar el error o manejarlo según tus necesidades
            Log::error('Error al obtener el último nro_liqui: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene los distintos períodos fiscales formateados como YYYYMM.
     */
    public static function getPeriodosFiscales(): array
    {
        return self::query()
            ->select('per_liano', 'per_limes')
            ->selectRaw("CONCAT(per_liano, LPAD(CAST(per_limes AS VARCHAR), 2, '0')) as periodo_fiscal")
            ->selectRaw("CONCAT(per_liano, LPAD(CAST(per_limes AS VARCHAR), 2, '0')) as periodo")
            ->distinct()
            ->orderBy('periodo_fiscal', 'desc')
            ->pluck('periodo', 'periodo_fiscal')
            ->toArray();
    }

    /**
     *  Metodo estatico para verificar si existe un nro_liqui en la tabla dh22.
     *
     * @param NroLiqui|int $nroLiqui
     *
     * @return bool True si la función se ejecutó correctamente, false en caso contrario.
     */
    public static function verificarNroLiqui($nroLiqui): bool
    {
        $nroLiquiValue = $nroLiqui instanceof NroLiqui ? $nroLiqui->value() : $nroLiqui;

        return static::where('nro_liqui', $nroLiquiValue)->exists();
    }

    /* ################################ ACCESORES Y MUTADORES ################################ */

    public function descLiqui(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => $this->attributes['desc_liqui'] = EncodingService::toLatin1($value),
        );
    }

    // ########################## SCOPES ###############################################
    public function scopeWithLiquidacion(Builder $query, int $nroLiqui): Builder
    {
        return $query->where('dh21.nro_liqui', $nroLiqui);
    }

    public function scopeWithPeriodoFiscal(Builder $query, string $periodoFiscal): Builder
    {
        $year = substr($periodoFiscal, 0, 4);
        $month = substr($periodoFiscal, 4, 2);

        return $query->where('per_liano', $year)
            ->where('per_limes', $month);
    }

    public function scopeAbierta($query)
    {
        return $query->where('sino_cerra', '!=', 'C'); // Asumiendo que 'C' significa cerrada
    }

    /**
     * Scope que filtra las liquidaciones por aquellas que tienen la descripción 'definitiva'.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeDefinitiva($query)
    {
        return $query->whereRaw("LOWER(desc_liqui) LIKE '%definitiva%'");
    }

    public function scopeDefinitivaCerrada($query)
    {
        return $query
            ->definitiva()
            ->where('sino_cerra', 'C');
    }

    /**
     * Scope para filtrar liquidaciones por rango de fechas.
     *
     * @param Builder $query
     * @param Carbon $fechaInicio
     * @param Carbon $fechaFin
     *
     * @return Builder
     */
    public function scopeBetweenPeriodoLiquidacion($query, $fechaInicio, $fechaFin)
    {
        $añoInicio = $fechaInicio->year;
        $mesInicio = $fechaInicio->month;
        $añoFin = $fechaFin->year;
        $mesFin = $fechaFin->month;

        return $query->where(function ($q) use ($añoInicio, $mesInicio, $añoFin, $mesFin): void {
            if ($añoInicio === $añoFin) {
                $q->where('per_liano', $añoInicio)
                    ->whereBetween('per_limes', [$mesInicio, $mesFin]);
            } else {
                $q->where(function ($subQ) use ($añoInicio, $mesInicio, $añoFin, $mesFin): void {
                    $subQ->where(function ($innerQ) use ($añoInicio, $mesInicio): void {
                        $innerQ->where('per_liano', $añoInicio)
                            ->where('per_limes', '>=', $mesInicio);
                    })->orWhere(function ($innerQ) use ($añoFin, $mesFin): void {
                        $innerQ->where('per_liano', $añoFin)
                            ->where('per_limes', '<=', $mesFin);
                    })->orWhere(function ($innerQ) use ($añoInicio, $añoFin): void {
                        $innerQ->whereBetween('per_liano', [$añoInicio + 1, $añoFin - 1]);
                    });
                });
            }
        });
    }

    /**
     * Scope para filtrar liquidaciones que generan datos impositivos.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeGeneraImpositivo($query)
    {
        return $query->where('sino_genimp', true);
    }

    /**
     * Scope para filtrar liquidaciones por período fiscal.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeFilterByYearMonth($query, PeriodoFiscal|int $year, PeriodoFiscal|int $month)
    {
        if ($year instanceof PeriodoFiscal) {
            return $query->where('per_liano', $year->year())
                ->where('per_limes', $year->month());
        }

        return $query->where('per_liano', $year)
            ->where('per_limes', $month);
    }

    /**
     * Filtra las liquidaciones por un periodo fiscal específico en formato año/mes.
     *
     * @param \Illuminate\Database\Eloquent\Builder<self> $query
     * @param array|PeriodoFiscal|null $periodoFiscal Array con ['year' => año, 'month' => mes]
     *
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeFilterByPeriodoFiscal($query,array|PeriodoFiscal|null $periodoFiscal = null): Builder
    {
        if (!$periodoFiscal) {
            return $query;
        }

        if ($periodoFiscal instanceof PeriodoFiscal) {
            return $query->whereRaw(
                "CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) = ?",
                [$periodoFiscal->toString()],
            );
        }

        return $query->whereRaw(
            "CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) = ?",
            [
                $periodoFiscal['year'] . str_pad((string) $periodoFiscal['month'], 2, '0', \STR_PAD_LEFT),
            ],
        );
    }

    /**
     * Obtiene las liquidaciones formateadas como "nro_liqui - desc_liqui" para mostrar en selects.
     *
     * @param \Illuminate\Database\Eloquent\Builder<self> $query
     *
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeFormateadoParaSelect($query)
    {
        return $query->select('nro_liqui', 'desc_liqui');
    }

    /**
     * Obtiene liquidaciones filtradas por periodo fiscal y formateadas para un select.
     *
     * @param array<string, int|string>|null $periodoFiscal Array con claves 'year' y 'month'
     *
     * @return SupportCollection<int, non-falsy-string>
     */
    public static function getLiquidacionesByPeriodoFiscal(?array $periodoFiscal = null): SupportCollection
    {
        return static::query()
            ->select('nro_liqui', 'desc_liqui')
            ->filterByPeriodoFiscal($periodoFiscal)
            ->orderByDesc('nro_liqui')
            ->get()
            ->mapWithKeys(function ($liquidacion) {
                $descripcion = trim((string) $liquidacion->desc_liqui ?: '');
                return [
                    $liquidacion->nro_liqui => "{$liquidacion->nro_liqui} - {$descripcion}",
                ];
            });
    }

    /**
     * Accessor para descripcion_completa que combina nro_liqui y desc_liqui con encoding seguro.
     *
     * @return Attribute<string, never>
     *
     * Este accesor devuelve una cadena que combina el número de liquidación (`nro_liqui`) y la descripción de la liquidación (`desc_liqui`).
     * Si `desc_liqui` es nulo, se utiliza una cadena vacía por defecto.
     * La descripción completa se formatea como "nro_liqui - desc_liqui".
     *
     * @see EncodingService::toUtf8()
     * @see EncodingService::toLatin1()
     */
    protected function descripcionCompleta(): Attribute
    {
        return Attribute::make(get: fn (): string => "{$this->nro_liqui} - " . ($this->desc_liqui ?? ''));
    }

    /**
     * Atributo que obtiene el período fiscal en formato YYYYMM a partir de las propiedades `perli_ano` y *`perli_mes` del modelo.
     * Accesor para el atributo 'periodo_fiscal'.
     *
     * @return Attribute<string, never>
     **/
    protected function periodoFiscal(): Attribute
    {
        return Attribute::make(
            get: fn (): string => "(string) $this->per_liano" . str_pad((string) $this->per_limes, 2, '0', \STR_PAD_LEFT),
        );
    }

    /**
     * Atributo que obtiene el período fiscal como un objeto PeriodoFiscal.
     *
     * @return Attribute<PeriodoFiscal, never>
     */
    protected function periodoFiscalObject(): Attribute
    {
        return Attribute::make(
            get: fn (): PeriodoFiscal => new PeriodoFiscal($this->per_liano, $this->per_limes),
        );
    }

    /**
     * Atributo que obtiene y establece el período fiscal en formato YYYYMM a partir de las propiedades `per_liano` y `per_limes` del modelo.
     *
     * El método `get` (Accessor) devuelve el período fiscal en formato YYYYMM concatenando los valores de `per_liano` y `per_limes` con el formato adecuado.
     * El método `set` (Mutator) establece los valores de `per_liano` y `per_limes` a partir de un valor de período fiscal en formato YYYYMM.
     *
     * @return Attribute<string, array{per_liano: int, per_limes: int}>
     */
    protected function periodo(): Attribute
    {
        return Attribute::make(
            get: fn (): string => "{(string)$this->per_liano}" . str_pad((string) $this->per_limes, 2, '0', \STR_PAD_LEFT),
            set: function ($value): array {
                if ($value instanceof PeriodoFiscal) {
                    return [
                        'per_liano' => $value->year(),
                        'per_limes' => $value->month(),
                    ];
                }

                return [
                    'per_liano' => substr($value, 0, 4),
                    'per_limes' => substr($value, 4, 2),
                ];
            },
        );
    }

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected function casts(): array
    {
        return [
            'fec_ultap' => 'date',
            'fec_emisi' => 'date',
            'sino_aguin' => 'boolean',
            'sino_reten' => 'boolean',
            'sino_genimp' => 'boolean',
            'per_liano' => 'integer',
            'per_limes' => 'integer',
        ];
    }
}
