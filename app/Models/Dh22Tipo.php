<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class Dh22Tipo extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'mapuche.dh22_tipos';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'desc_corta',
        'desc_larga'
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function liquidaciones()
    {
        return $this->hasMany(Dh22::class, 'id_tipo_liqui', 'id');
    }
}
