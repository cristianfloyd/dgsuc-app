<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfipRelacionesActivasCrudo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'afip_relaciones_activas_crudo';

    protected $fillable = [
        'id',
        'linea_completa',
    ];
}
