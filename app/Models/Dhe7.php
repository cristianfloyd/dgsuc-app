<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dhe7 extends Model
{
    use HasFactory;

    use MapucheConnectionTrait;
    
    protected $table = 'dhe7';

    // Especifica la clave primaria de la tabla
    protected $primaryKey = 'codigoaccesoescalafon';

    // Indica que la clave primaria no es un incremento automático
    public $incrementing = false;

    // Especifica el tipo de clave primaria
    protected $keyType = 'string';

    // Desactiva las marcas de tiempo automáticas (created_at, updated_at)
    public $timestamps = false;

    // Define los atributos que se pueden asignar en masa
    protected $fillable = [
        'codigoaccesoescalafon',
        'descaccesoescalafon',
    ];
}
