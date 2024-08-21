<?php

namespace App\Models\Mapuche;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\EstadoLiquidacionModel;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent para la tabla mapuche.dh22
 *
 * Esta clase representa la tabla de liquidaciones en el sistema.
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
    ];

    /**
     * Obtiene el tipo de liquidación asociado.
     */
    public function tipoLiquidacion()
    {
        return $this->belongsTo(Dh22Tipo::class, 'id_tipo_liqui', 'id');
    }

    public static function getLiquidacionesForWidget()
    {
        return static::query()
            ->select('nro_liqui',
                DB::raw("CONCAT(per_liano,per_limes) as periodo_fiscal"),
            'desc_liqui')
            ->orderBy('nro_liqui', 'desc');
    }

    /**
     * Obtiene el estado de liquidación asociado.
     */
    public function estadoLiquidacion()
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
}
