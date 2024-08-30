<?php

namespace App\Models;

use App\Traits\HasCompositePrimaryKey;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh61 extends Model
{
    use HasCompositePrimaryKey;

    protected $connection = 'pgsql-mapuche';

    // Definimos la tabla asociada al modelo
    protected $table = 'mapuche.dh61';
    public $timestamps = false;

    // Definimos la clave primaria compuesta
    protected $primaryKey = ['codc_categ', 'vig_caano', 'vig_cames'];
    public $incrementing = false;
    protected $keyType = 'string';

    // Definir los atributos que se pueden asignar en masa
    protected $fillable = [
        'codc_categ',
        'equivalencia',
        'tipo_escal',
        'nro_escal',
        'impp_basic',
        'codc_dedic',
        'sino_mensu',
        'sino_djpat',
        'vig_caano',
        'vig_cames',
        'desc_categ',
        'sino_jefat',
        'impp_asign',
        'computaantig',
        'controlcargos',
        'controlhoras',
        'controlpuntos',
        'controlpresup',
        'horasmenanual',
        'cantpuntos',
        'estadolaboral',
        'nivel',
        'tipocargo',
        'remunbonif',
        'noremunbonif',
        'remunnobonif',
        'noremunnobonif',
        'otrasrem',
        'dto1610',
        'reflaboral',
        'refadm95',
        'critico',
        'jefatura',
        'gastosrepre',
        'codigoescalafon',
        'noinformasipuver',
        'noinformasirhu',
        'imppnooblig',
        'aportalao',
        'factor_hs_catedra',
    ];

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
