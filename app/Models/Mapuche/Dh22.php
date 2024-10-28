<?php

namespace App\Models\Mapuche;

use Illuminate\Support\Facades\DB;
use App\ValueObjects\PeriodoFiscal;
use Illuminate\Support\Facades\Log;
use App\Models\EstadoLiquidacionModel;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    use MapucheConnectionTrait;

    /**
     * Indica el nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'dh22';

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



    /** Obtiene el período fiscal como una cadena de texto en el formato "AAAAMM".
    *
    * Este método de acceso (accessor) se utiliza para generar una representación
    * de cadena del período fiscal a partir de los atributos `per_liano` y `per_limes`
    * del modelo. El formato resultante es "AAAAMM", donde "AAAA" representa el año
    * y "MM" representa el mes con un relleno de cero a la izquierda si es necesario.
    */
    // public function getPeriodoFiscalAttribute(): string
    // {
    //     return $this->per_liano . str_pad($this->per_limes, 2, '0', STR_PAD_LEFT);
    // }


    /**
     * Obtiene el tipo de liquidación asociado.
     */
    public function tipoLiquidacion(): BelongsTo
    {
        return $this->belongsTo(Dh22Tipo::class, 'id_tipo_liqui', 'id');
    }

    public static function getLiquidacionesForWidget(): Builder
    {

        return self::select(
            'nro_liqui',
            DB::raw("CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) as periodo_fiscal"),
            'desc_liqui'
            )->orderByDesc('nro_liqui');
    }


    /**
     * Obtiene el estado de liquidación asociado.
     */
    public function estadoLiquidacion(): BelongsTo
    {
        return $this->belongsTo(EstadoLiquidacionModel::class, 'sino_cerra', 'cod_estado_liquidacion');
    }

    public function scopeAbierta($query)
    {
        return $query->where('sino_cerra', '!=', 'C'); // Asumiendo que 'C' significa cerrada
    }

    public function scopeDefinitiva($query)
    {
        return $query->whereRaw("LOWER(desc_liqui) LIKE '%definitiva%'");
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
     *  Metodo estatico para verificar si existe un nro_liqui en la tabla dh22.
     *
     * @param int $nroLiqui
     * @return bool True si la función se ejecutó correctamente, false en caso contrario.
     */
    public static function verificarNroLiqui(int $nroLiqui): bool
    {
        return static::where('nro_liqui', $nroLiqui)->exists();
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
    * Atributo que obtiene el período fiscal en formato YYYYMM a partir de las propiedades `perli_ano` y *`perli_mes` del modelo.
    **/
    protected function periodoFiscal(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => new PeriodoFiscal(
                año: $attributes['per_liano'],
                mes: $attributes['per_limes']
            )
        );
    }

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
}
