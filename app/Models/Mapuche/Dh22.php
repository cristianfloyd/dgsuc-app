<?php

namespace App\Models\Mapuche;

use Illuminate\Support\Carbon;
use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use App\ValueObjects\PeriodoFiscal;
use Illuminate\Support\Facades\Log;
use App\Traits\Mapuche\EncodingTrait;
use App\Models\EstadoLiquidacionModel;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Eloquent para la tabla mapuche.dh22
 *
 * Esta clase representa la tabla de liquidaciones en el sistema.
 * @method static select(string $string)
 * @method static orderBy(string $string, string $string1)
 * @method static where(string $string, int $nroLiqui)
 */
class Dh22 extends Model
{
    use MapucheConnectionTrait, HasFactory, EncodingTrait;

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
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'per_liano', 'per_limes', 'desc_liqui', 'fec_ultap', 'per_anoap', 'per_mesap',
        'desc_lugap', 'fec_emisi', 'desc_emisi', 'vig_emano', 'vig_emmes', 'vig_caano',
        'vig_cames', 'vig_coano', 'vig_comes', 'codn_econo', 'sino_cerra', 'sino_aguin',
        'sino_reten', 'sino_genimp', 'nrovalorpago', 'finimpresrecibos', 'id_tipo_liqui'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fec_ultap' => 'date',
        'fec_emisi' => 'date',
        'sino_aguin' => 'boolean',
        'sino_reten' => 'boolean',
        'sino_genimp' => 'boolean',
        'per_liano' => 'integer',
        'per_limes' => 'integer',
    ];

    /**
     * Campos que requieren conversión de codificación
     */
    protected $encodedFields = [
        'desc_liqui',
    ];


    /**
     * Obtiene el tipo de liquidación asociado.
     */
    public function tipoLiquidacion(): BelongsTo
    {
        return $this->belongsTo(Dh22Tipo::class, 'id_tipo_liqui', 'id');
    }

    /**
     * Obtiene el estado de liquidación asociado.
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
    public static function getLiquidacionesForWidget(): Builder
    {
        return self::query()
            ->select('nro_liqui')
            ->selectRaw("CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) as periodo_fiscal")
            ->addSelect('desc_liqui')
            ->orderByDesc('nro_liqui');
    }


    public static function getDescripcionLiquidacion($nro_liqui): string
    {
        return static::select('desc_liqui')
            ->where('nro_liqui', $nro_liqui)
            ->first()
            ->desc_liqui;
    }

    /**
     * Obtiene la ultima liquidación abierta.
     *
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
     *
     * @return \Illuminate\Database\Eloquent\Collection
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
     *
     * @return int
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
     *
     * @return array
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
     * @param int $nroLiqui
     * @return bool True si la función se ejecutó correctamente, false en caso contrario.
     */
    public static function verificarNroLiqui(int $nroLiqui): bool
    {
        return static::where('nro_liqui', $nroLiqui)->exists();
    }

    /* ################################ ACCESORES Y MUTADORES ################################ */
    /**
     * Mutador para convertir desc_liqui a UTF-8 al obtener el valor
     */
    public function getDescLiquiAttribute($value)
    {
        return EncodingService::toUtf8(trim($value));
    }

   public function descLiqui(): Attribute
   {
    return Attribute::make(
        get: fn($value) => EncodingService::toUtf8($value),
        set: fn($value) => $this->attributes['desc_liqui'] = EncodingService::toLatin1($value),
    );
   }

    /**
     * Atributo que obtiene el período fiscal en formato YYYYMM a partir de las propiedades `perli_ano` y *`perli_mes` del modelo.
     * Accesor para el atributo 'periodo_fiscal'.
    **/
    protected function periodoFiscal(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->per_liano}".str_pad($this->per_limes, 2, '0', STR_PAD_LEFT),
        );
    }

    /**
     * Atributo que obtiene y establece el período fiscal en formato YYYYMM a partir de las propiedades `per_liano` y `per_limes` del modelo.
     *
     * El método `get` (Accessor) devuelve el período fiscal en formato YYYYMM concatenando los valores de `per_liano` y `per_limes` con el formato adecuado.
     * El método `set` (Mutator) establece los valores de `per_liano` y `per_limes` a partir de un valor de período fiscal en formato YYYYMM.
     */
    protected function periodo(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->per_liano}".str_pad($this->per_limes, 2, '0', STR_PAD_LEFT),
            set: fn(string $value) => [
                'per_liano' => substr($value, 0, 4),
                'per_limes' => substr($value, 4, 2),
            ]
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
     * Scope para filtrar liquidaciones por rango de fechas
     *
     * @param Builder $query
     * @param Carbon $fechaInicio
     * @param Carbon $fechaFin
     * @return Builder
     */
    public function scopeBetweenPeriodoLiquidacion($query, $fechaInicio, $fechaFin)
    {
        $añoInicio = $fechaInicio->year;
        $mesInicio = $fechaInicio->month;
        $añoFin = $fechaFin->year;
        $mesFin = $fechaFin->month;

        return $query->where(function ($q) use ($añoInicio, $mesInicio, $añoFin, $mesFin) {
            if ($añoInicio === $añoFin) {
                $q->where('per_liano', $añoInicio)
                  ->whereBetween('per_limes', [$mesInicio, $mesFin]);
            } else {
                $q->where(function ($subQ) use ($añoInicio, $mesInicio, $añoFin, $mesFin) {
                    $subQ->where(function ($innerQ) use ($añoInicio, $mesInicio) {
                        $innerQ->where('per_liano', $añoInicio)
                               ->where('per_limes', '>=', $mesInicio);
                    })->orWhere(function ($innerQ) use ($añoFin, $mesFin) {
                        $innerQ->where('per_liano', $añoFin)
                               ->where('per_limes', '<=', $mesFin);
                    })->orWhere(function ($innerQ) use ($añoInicio, $añoFin) {
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
     * @param int $year
     * @param int $month
     * @return Builder
     */
    public function scopePeriodoFiscal($query, int $year, int $month)
    {
        return $query->where('per_liano', $year)
                    ->where('per_limes', $month);
    }

    /**
     * Filtra las liquidaciones por un periodo fiscal específico en formato año/mes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $periodoFiscal Array con ['year' => año, 'month' => mes]
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByPeriodoFiscal($query, ?array $periodoFiscal)
    {
        if (!$periodoFiscal) {
            return $query;
        }

        return $query->whereRaw(
            "CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) = ?",
            [
                $periodoFiscal['year'] . str_pad($periodoFiscal['month'], 2, '0', STR_PAD_LEFT)
            ]
        );
    }

    /**
     * Obtiene las liquidaciones formateadas como "nro_liqui - desc_liqui" para mostrar en selects.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFormateadoParaSelect($query)
    {
        return $query->selectRaw("nro_liqui, CONCAT(nro_liqui, ' - ', desc_liqui) as descripcion_completa");
    }

    /**
     * Obtiene liquidaciones filtradas por periodo fiscal y formateadas para un select.
     *
     * @param array|null $periodoFiscal
     * @return \Illuminate\Support\Collection
     */
    public static function getLiquidacionesByPeriodoFiscal(?array $periodoFiscal = null)
    {
        return static::getLiquidacionesForWidget()
            ->filterByPeriodoFiscal($periodoFiscal)
            ->formateadoParaSelect()
            ->pluck('descripcion_completa', 'nro_liqui');
    }
}
