<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'mapuche.dh01';
    protected $primaryKey = 'nro_legaj';
    public $incrementing = false;
    protected $keyType = 'integer';
    public $timestamps = false;
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
        'pais_nacim'
    ];

    /**
     * Defina la relacion con la tabla pais (dha3).
     */
    // public function country()
    // {
    //     return $this->belongsTo(Dha3::class, 'pais_nacim', 'codigo_pais');
    // }

    /**
     * Define la relacion con la tabla (dha5).
     */
    // public function province()
    // {
    //     return $this->belongsTo(Dha5::class, 'pcia_nacim', 'codigo_pcia');
    // }


}
