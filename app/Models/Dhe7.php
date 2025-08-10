<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dhe7 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    // Indica que la clave primaria no es un incremento automático
    public $incrementing = false;

    // Desactiva las marcas de tiempo automáticas (created_at, updated_at)
    public $timestamps = false;

    protected $table = 'dhe7';

    // Especifica la clave primaria de la tabla
    protected $primaryKey = 'codigoaccesoescalafon';

    // Especifica el tipo de clave primaria
    protected $keyType = 'string';

    // Define los atributos que se pueden asignar en masa
    protected $fillable = [
        'codigoaccesoescalafon',
        'descaccesoescalafon',
    ];
}
