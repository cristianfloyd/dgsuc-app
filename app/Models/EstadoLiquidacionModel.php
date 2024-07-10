<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoLiquidacionModel extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'mapuche.estado_liquidacion';
    public $timestamps = false;

    protected $fillable = [
        'cod_estado_liquidacion',
        'desc_estado_liquidacion'
    ];

}
