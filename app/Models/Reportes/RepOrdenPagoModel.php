<?php

namespace App\Models\Reportes;

use App\Traits\MapucheSchemaSuc;
use Illuminate\Database\Eloquent\Model;

class RepOrdenPagoModel extends Model
{
    use MapucheSchemaSuc;

    /**
     * Tabla de la base de datos utilizada por el modelo RepOrdenPago.
     */
    protected $table = 'suc.rep_orden_pago';


    /**
     * Indica que el modelo RepOrdenPago no tiene campos de fecha de creación y actualización.
     */
    public $timestamps = false;


    /**
     * Los atributos que se pueden rellenar masivamente.
     * @var array
     */
    protected $fillable = [
        'nro_liqui',
        'banco',
        'codn_funci',
        'codn_fuent',
        'codc_uacad',
        'caracter',
        'codn_progr',
        'remunerativo',
        'no_remunerativo',
        'descuentos',
        'aportes',
        'estipendio',
        'med_resid',
        'productividad',
        'sal_fam',
        'hs_extras',
        'total',
    ];

    /**
     * Indica los tipos de datos que deben ser convertidos automáticamente al acceder a los atributos del modelo.
     * @var array
     */
    protected $casts = [
        'nro_liqui' => 'integer',
        'banco' => 'integer',
        'remunerativo' => 'decimal:2',
        'no_remunerativo' => 'decimal:2',
        'descuentos' => 'decimal:2',
        'aportes' => 'decimal:2',
        'estipendio' => 'decimal:2',
        'med_resid' => 'decimal:2',
        'productividad' => 'decimal:2',
        'sal_fam' => 'decimal:2',
        'hs_extras' => 'decimal:2',
        'total' => 'decimal:2',
    ];
}
