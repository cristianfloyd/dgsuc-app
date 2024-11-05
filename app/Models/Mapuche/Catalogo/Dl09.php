<?php

namespace App\Models\Mapuche\Catalogo;

use App\Models\Mapuche\Dh05;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dl09 extends Model
{
    use MapucheConnectionTrait;

    // Especificar la tabla asociada al modelo
    public $incrementing = false;

    // Especificar la clave primaria
    public $timestamps = false;

    // Indicar que la clave primaria no es auto-incremental
    protected $table = 'dl09';

    // Indicar que la clave primaria es de tipo string
    protected $primaryKey = 'nombre_tipo_norma';

    // Deshabilitar timestamps si no existen en la tabla
    protected $keyType = 'string';

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
