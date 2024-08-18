<?php

namespace App\Models;

use App\Models\Mapuche\Dh05;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dl09 extends Model
{
    use MapucheConnectionTrait;

    // Especificar la tabla asociada al modelo
    protected $table = 'mapuche.dl09';

    // Especificar la clave primaria
    protected $primaryKey = 'nombre_tipo_norma';

    // Indicar que la clave primaria no es auto-incremental
    public $incrementing = false;

    // Indicar que la clave primaria es de tipo string
    protected $keyType = 'string';

    // Deshabilitar timestamps si no existen en la tabla
    public $timestamps = false;

    // Especificar los campos que se pueden asignar masivamente
    protected $fillable = [
        'nombre_tipo_norma',
    ];

    /**
     * Relación con el modelo Dh05
     */
    public function dh05s(): HasMany
    {
        return $this->hasMany(Dh05::class, 'tipo_norma_alta', 'nombre_tipo_norma');
    }
}
