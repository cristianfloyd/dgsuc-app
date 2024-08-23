<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfipMapucheArt extends Model
{
    use MapucheConnectionTrait;
    
    protected $table = 'suc.afip_art';
    protected $primaryKey = 'cuil_original';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'cuil_formateado',
        'cuil_original',
        'apellido_y_nombre',
        'nacimiento',
        'sueldo',
        'sexo',
        'nro_legaj',
        'establecimiento',
        'tarea',
        'conce'
    ];

    protected $casts = [
        'nacimiento' => 'date',
        'nro_legaj' => 'integer',
        'conce' => 'integer'
    ];
}
