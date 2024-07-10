<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dh01 extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'mapuche.dh01';
    public $timestamps = false;
    protected $primaryKey = 'nro_legaj';

    protected $fillable = [
        'nro_legaj', 'desc_appat', 'desc_apmat', 'desc_apcas', 'desc_nombr', 'nro_tabla',
        'tipo_docum', 'nro_docum',
        'nro_cuil1', 'nro_cuil', 'nro_cuil2',
        'tipo_sexo', 'fec_nacim', 'tipo_facto', 'tipo_rh', 'nro_ficha', 'tipo_estad', 'nombrelugarnac',
        'periodoalta', 'anioalta', 'periodoactualizacion', 'anioactualizacion', 'pcia_nacim', 'pais_nacim'
    ];

    // Definir las relaciones
    public function dh03()
    {
        return $this->hasMany(dh03::class, 'nro_legaj', 'nro_legaj');
    }

    public function dh21()
    {
        return $this->hasMany(Dh21::class, 'nro_legaj', 'nro_legaj');
    }

    public function scopeSearch($query, $val)
    {
        return $query->where('nro_legaj', 'like', '%'.$val.'%')
            ->orWhere('nro_cuil', 'like', '%'.$val.'%')
            ->orWhere('desc_appat', 'like', '%'.strtoupper($val).'%')
            ->orWhere('desc_apmat', 'like', '%'.strtoupper($val).'%')
            ->orWhere('desc_apcas', 'like', '%'.strtoupper($val).'%')
            ->orWhere('desc_nombr', 'like', '%'.strtoupper($val).'%');
    }

    public function afipMapucheSicoss(): HasOne
    {
        return $this->hasOne(AfipMapucheSicoss::class, 'cuil', 'cuil_completo');
    }

}


