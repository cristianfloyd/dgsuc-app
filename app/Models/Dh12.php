<?php

namespace App\Models;

use App\Models\Dh13;
use App\Enums\TipoNove;
use App\Enums\TipoConce;
use App\Enums\TipoDistr;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dh12 extends Model
{
    use MapucheConnectionTrait;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dh12';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'codn_conce';

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
        'vig_coano', 'vig_comes', 'desc_conce', 'desc_corta', 'tipo_conce',
        'codc_vige1', 'desc_nove1', 'tipo_nove1', 'cant_ente1', 'cant_deci1',
        'codc_vige2', 'desc_nove2', 'tipo_nove2', 'cant_ente2', 'cant_deci2',
        'flag_acumu', 'flag_grupo', 'nro_orcal', 'nro_orimp', 'sino_legaj',
        'tipo_distr', 'tipo_ganan', 'chk_acumsac', 'chk_acumproy', 'chk_dcto3',
        'chkacumprhbrprom', 'subcicloliquida', 'chkdifhbrcargoasoc',
        'chkptesubconcep', 'chkinfcuotasnovper', 'genconimp0', 'sino_visible'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'tipo_conce' => TipoConce::class,
        'tipo_nove1' => TipoNove::class,
        'tipo_nove2' => TipoNove::class,
        'tipo_distr' => TipoDistr::class,
        'chk_acumsac' => 'boolean',
        'chk_acumproy' => 'boolean',
        'chk_dcto3' => 'boolean',
        'chkacumprhbrprom' => 'boolean',
        'chkdifhbrcargoasoc' => 'boolean',
        'chkptesubconcep' => 'boolean',
        'chkinfcuotasnovper' => 'boolean',
        'genconimp0' => 'boolean',
    ];

    /**
     * Obtiene los Dh13 asociados con este Dh12.
     */
    public function dh13s(): HasMany
    {
        return $this->hasMany(Dh13::class, 'codn_conce', 'codn_conce');
    }
}
