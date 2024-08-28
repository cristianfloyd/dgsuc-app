<?php

namespace App\Models;

use App\Traits\HasCompositePrimaryKey;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh61 extends Model
{
    use MapucheConnectionTrait, HasCompositePrimaryKey;

    // Definimos la tabla asociada al modelo
    protected $table = 'dh61';

    // Definimos la clave primaria compuesta
    protected $primaryKey = ['codc_categ', 'vig_caano', 'vig_cames'];
    public $incrementing = false;
    protected $keyType = 'string';

    // Definimos los tipos de datos de las columnas
    protected $casts = [
        'codc_categ' => 'string',
        'equivalencia' => 'string',
        'tipo_escal' => 'string',
        'nro_escal' => 'integer',
        'impp_basic' => 'decimal:2',
        'codc_dedic' => 'string',
        'sino_mensu' => 'string',
        'sino_djpat' => 'string',
        'vig_caano' => 'integer',
        'vig_cames' => 'integer',
        'desc_categ' => 'string',
        'sino_jefat' => 'string',
        'impp_asign' => 'decimal:2',
        'computaantig' => 'integer',
        'controlcargos' => 'boolean',
        'controlhoras' => 'integer',
        'controlpuntos' => 'integer',
        'controlpresup' => 'integer',
        'horasmenanual' => 'string',
        'cantpuntos' => 'integer',
        'estadolaboral' => 'string',
        'nivel' => 'string',
        'tipocargo' => 'string',
        'remunbonif' => 'float',
        'noremunbonif' => 'float',
        'remunnobonif' => 'float',
        'noremunnobonif' => 'float',
        'otrasrem' => 'float',
        'dto1610' => 'float',
        'reflaboral' => 'float',
        'refadm95' => 'float',
        'critico' => 'float',
        'jefatura' => 'float',
        'gastosrepre' => 'float',
        'codigoescalafon' => 'string',
        'noinformasipuver' => 'integer',
        'noinformasirhu' => 'integer',
        'imppnooblig' => 'integer',
        'aportalao' => 'integer',
        'factor_hs_catedra' => 'float',
    ];

     // Relación con el modelo Dh11 utilizando la clave primaria compuesta
    public function dh11(): BelongsTo
    {
        return $this->compositeBelongsTo(
            Dh11::class,
            ['codc_categ', 'vig_caano', 'vig_cames'],
            ['codc_categ', 'vig_caano', 'vig_cames']
        );
    }

}
