<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class EstadoLiquidacion extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'mapuche.estado_liquidacion';
    protected $primaryKey = 'cod_estado_liquidacion';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_estado_liquidacion',
        'desc_estado_liquidacion'
    ];

    public function liquidaciones()
    {
        return $this->hasMany(Dh22::class, 'sino_cerra', 'cod_estado_liquidacion');
    }
}
