<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class Dh04 extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'mapuche.dh04';
    protected $primaryKey = 'nro_otra_actividad';
    public $timestamps = false;

    protected $fillable = [
        'nro_legaj', 'tipo_activ', 'desc_entid', 'nro_cuit', 'desc_cargo',
        'cant_hs', 'fec_ingre', 'fec_egres', 'codc_dedic', 'vig_otano',
        'vig_otmes', 'nro_otra_actividad', 'dominstitucion', 'relprofesion',
        'aporta_antig_remun', 'aporta_antig_lao', 'aporta_ant_jubil',
        'mes_vigencia', 'anio_vigencia', 'codmotivobaja', 'codescalafonoa',
        'codcategoriaoa', 'codsistemaacceso', 'codgradooa'
    ];

    protected $casts = [
        'nro_legaj' => 'integer',
        'nro_cuit' => 'decimal:0',
        'cant_hs' => 'integer',
        'fec_ingre' => 'date',
        'fec_egres' => 'date',
        'vig_otano' => 'integer',
        'vig_otmes' => 'integer',
        'nro_otra_actividad' => 'integer',
        'relprofesion' => 'boolean',
        'aporta_antig_remun' => 'boolean',
        'aporta_antig_lao' => 'boolean',
        'aporta_ant_jubil' => 'boolean',
        'mes_vigencia' => 'integer',
        'anio_vigencia' => 'integer',
        'codmotivobaja' => 'integer',
    ];

    public function dh01()
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    // public function dhb3()
    // {
    //     return $this->belongsTo(Dhb3::class, 'codmotivobaja', 'codigo');
    // }

    // public function dhe5()
    // {
    //     return $this->belongsTo(Dhe5::class, 'codescalafonoa', 'codigoescalafonoa');
    // }

    // public function dhe6()
    // {
    //     return $this->belongsTo(Dhe6::class, 'codcategoriaoa', 'codigocategoriaoa');
    // }

    // public function dhe7()
    // {
    //     return $this->belongsTo(Dhe7::class, 'codsistemaacceso', 'codigoaccesoescalafon');
    // }

    // public function dhe8()
    // {
    //     return $this->belongsTo(Dhe8::class, 'codgradooa', 'codigogradooa');
    // }
}

