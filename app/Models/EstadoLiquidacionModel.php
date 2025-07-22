<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class EstadoLiquidacionModel extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = false;

    protected $table = 'estado_liquidacion';

    protected $fillable = [
        'cod_estado_liquidacion',
        'desc_estado_liquidacion',
    ];
}
