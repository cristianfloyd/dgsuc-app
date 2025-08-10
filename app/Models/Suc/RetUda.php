<?php

namespace App\Models\Suc;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetUda extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indica que la clave primaria no es auto-incrementable.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'suc.ret_uda';

    /**
     * La clave primaria compuesta para la tabla.
     *
     * @var array
     */
    protected $primaryKey = ['nro_legaj', 'nro_cargo', 'periodo'];

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'nro_legaj',
        'nro_cargo',
        'periodo',
        'tipo_escal',
        'codc_categ',
        'codc_agrup',
        'codc_carac',
        'porc_aplic',
        'codc_dedic',
        'hs_cat',
        'antiguedad',
        'permanencia',
        'porchaber',
        'lic_50',
        'impp_basic',
        'zona_desf',
        'riesgo',
        'falla_caja',
        'ded_excl',
        'titu_nivel',
        'subrog',
        'cat_108',
        'basico_108',
        'nro_liqui',
        'cat_basico_7',
        'cat_basico_v_perm',
        'codc_uacad',
        'coddependesemp',
        'adi_col_sec',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'porc_aplic' => 'float',
        'hs_cat' => 'float',
        'antiguedad' => 'float',
        'permanencia' => 'float',
        'porchaber' => 'float',
        'impp_basic' => 'decimal:2',
        'basico_108' => 'decimal:2',
        'cat_basico_7' => 'decimal:2',
        'cat_basico_v_perm' => 'decimal:2',
    ];
}
