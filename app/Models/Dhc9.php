<?php

namespace App\Models;

use App\Models\Dh03;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dhc9 extends Model
{
    use MapucheConnectionTrait;

    // Especificar la tabla asociada al modelo
    protected $table = 'dhc9';

    // Especificar la clave primaria
    protected $primaryKey = 'codagrup';

    // Indicar que la clave primaria no es auto-incremental
    public $incrementing = false;

    // Indicar que la clave primaria es de tipo string
    protected $keyType = 'string';

    // Deshabilitar timestamps si no existen en la tabla
    public $timestamps = false;

    // Especificar los campos que se pueden asignar masivamente
    protected $fillable = [
        'codagrup',
        'descagrup',
        'codigo_sirhu',
    ];

    /**
     * Relación con el modelo Dh03
     */
    public function dh03s(): HasMany
    {
        return $this->hasMany(Dh03::class, 'codc_agrup', 'codagrup');
    }
}
