<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use MapucheConnectionTrait;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'mapuche.dh01';

    protected $primaryKey = 'nro_legaj';

    protected $keyType = 'integer';

    protected $fillable = [
        'nro_legaj',
        'desc_appat',
        'desc_apmat',
        'desc_apcas',
        'desc_nombr',
        'nro_tabla',
        'tipo_docum',
        'nro_docum',
        'nro_cuil1',
        'nro_cuil',
        'nro_cuil2',
        'tipo_sexo',
        'fec_nacim',
        'tipo_facto',
        'tipo_rh',
        'nro_ficha',
        'tipo_estad',
        'nombrelugarnac',
        'periodoalta',
        'anioalta',
        'periodoactualizacion',
        'anioactualizacion',
        'pcia_nacim',
        'pais_nacim',
    ];
}
