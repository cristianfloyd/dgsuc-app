<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dhd7 extends Model
{
    use MapucheConnectionTrait;

    // Especificar la tabla asociada al modelo
    protected $table = 'dhd7';

    // Especificar la clave primaria
    protected $primaryKey = 'cod_clasif_cargo';

    // Indicar que la clave primaria no es auto-incremental
    public $incrementing = false;

    // Indicar que la clave primaria es de tipo entero
    protected $keyType = 'int';

    // Deshabilitar timestamps si no existen en la tabla
    public $timestamps = false;

    // Especificar los campos que se pueden asignar masivamente
    protected $fillable = [
        'cod_clasif_cargo',
        'desc_clasif_cargo',
    ];

    /**
     * Relación con el modelo Dh03
     */
    public function dh03s(): HasMany
    {
        return $this->hasMany(Dh03::class, 'cod_clasif_cargo', 'cod_clasif_cargo');
    }
}
