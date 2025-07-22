<?php

namespace App\Models;

use App\Models\Mapuche\Dh22;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class EstadoLiquidacion extends Model
{
    use MapucheConnectionTrait;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'estado_liquidacion';

    protected $primaryKey = 'cod_estado_liquidacion';

    protected $keyType = 'string';

    protected $fillable = [
        'cod_estado_liquidacion',
        'desc_estado_liquidacion',
    ];

    public function liquidaciones()
    {
        return $this->hasMany(Dh22::class, 'sino_cerra', 'cod_estado_liquidacion');
    }
}
