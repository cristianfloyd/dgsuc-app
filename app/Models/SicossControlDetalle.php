<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class SicossControlDetalle extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.sicoss_controles_detalle';

    protected $primaryKey = 'id';

    protected $fillable = [
        'control_id',
        'tipo_control',
        'cuil',
        'valor_sicoss',
        'valor_mapuche',
        'diferencia',
        'estado',
        'observaciones',
    ];
}
