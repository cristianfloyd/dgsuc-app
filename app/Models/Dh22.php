<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class Dh22 extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'mapuche.dh22';
    protected $primaryKey = 'nro_liqui';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nro_liqui',
        'per_liano',
        'per_limes',
        'desc_liqui',
        'fec_ultap',
        'per_anoap',
        'per_mesap',
        'desc_lugap',
        'fec_emisi',
        'desc_emisi',
        'vig_emano',
        'vig_emmes',
        'vig_caano',
        'vig_cames',
        'vig_coano',
        'vig_comes',
        'codn_econo',
        'sino_cerra',
        'sino_aguin',
        'sino_reten',
        'sino_genimp',
        'nrovalorpago',
        'finimpresrecibos',
        'id_tipo_liqui'
    ];

    protected $casts = [
        'nro_liqui' => 'integer',
        'per_liano' => 'integer',
        'per_limes' => 'integer',
        'per_anoap' => 'integer',
        'per_mesap' => 'integer',
        'vig_emano' => 'integer',
        'vig_emmes' => 'integer',
        'vig_caano' => 'integer',
        'vig_cames' => 'integer',
        'vig_coano' => 'integer',
        'vig_comes' => 'integer',
        'codn_econo' => 'integer',
        'sino_aguin' => 'boolean',
        'sino_reten' => 'boolean',
        'sino_genimp' => 'boolean',
        'nrovalorpago' => 'integer',
        'finimpresrecibos' => 'integer',
        'id_tipo_liqui' => 'integer',
        'fec_ultap' => 'date',
        'fec_emisi' => 'date',
    ];

    public function tipoLiquidacion()
    {
        return $this->belongsTo(Dh22Tipo::class, 'id_tipo_liqui', 'id');
    }

    public function estadoLiquidacion()
    {
        return $this->belongsTo(EstadoLiquidacion::class, 'sino_cerra', 'cod_estado_liquidacion');
    }


    /**
     * Obtiene una colección de liquidaciones para ser utilizadas en un widget.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getLiquidacionesForWidget()
    {
        return static::query()
            ->select('nro_liqui', 'desc_liqui')
            ->orderBy('nro_liqui', 'desc');
    }
}
