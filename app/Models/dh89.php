<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dh89 extends Model
{
    protected $connection = 'pgsql-mapuche';
    // Especifica la tabla asociada al modelo
    protected $table = 'mapuche.dh89';

    // Especifica la clave primaria de la tabla
    protected $primaryKey = 'nroesc';

    // Si la clave primaria no es autoincremental
    public $incrementing = false;

    // Si la clave primaria no es de tipo integer
    protected $keyType = 'int';

    // Indica si el modelo debe gestionar automáticamente los timestamps
    public $timestamps = true; // O false si no deseas timestamps

    // Especifica los atributos que pueden ser asignados masivamente
    protected $fillable = [
        'codigoescalafon',
        'nroorden',
        'codigoesc',
        'descesc',
        'ctrlgradooblig',
        'tipo_perm_tran',
        'infoadiccateg'
    ];

    protected $casts = [
        'nroesc' => 'integer',
        'codigoescalafon' => 'string',
        'nroorden' => 'integer',
        'codigoesc' => 'string',
        'descesc' => 'string',
        'ctrlgradooblig' => 'integer',
        'tipo_perm_tran' => 'string',
        'infoadiccateg' => 'string',
    ];
}
