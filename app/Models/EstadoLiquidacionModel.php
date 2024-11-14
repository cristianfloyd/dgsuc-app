<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class EstadoLiquidacionModel extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'estado_liquidacion';
    public $timestamps = false;

    protected $fillable = [
        'cod_estado_liquidacion',
        'desc_estado_liquidacion'
    ];

}
