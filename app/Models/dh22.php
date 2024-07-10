<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dh22 extends Model
{

    protected $connection = 'pgsql-mapuche';
    protected $table = 'mapuche.dh22';
    public $timestamps = false;
    protected $primaryKey = 'nro_liqui';
    protected $fillable = [
        'nro_liqui', 'per_liano', 'per_limes', 'desc_liqui', 'fec_ultap',
        'per_anoap', 'per_mesap', 'desc_lugap', 'fec_emisi', 'desc_emisi',
        'vig_emano', 'vig_emmes', 'vig_caano', 'vig_cames', 'vig_coano',
        'vig_comes', 'codn_econo', 'sino_cerra', 'sino_aguin', 'sino_reten',
        'sino_genimp', 'nrovalorpago', 'finimpresrecibos', 'id_tipo_liqui'
    ];

    /**
     * Obtiene el estado de liquidación asociado a este modelo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estadoLiquidacion()
    {
        return $this->belongsTo(EstadoLiquidacionModel::class, 'sino_cerra', 'cod_estado_liquidacion');
    }
}
