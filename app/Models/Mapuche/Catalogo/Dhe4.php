<?php

namespace App\Models\Mapuche\Catalogo;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Eloquent para la tabla 'mapuche.dhe4' que representa un organismo dentro del sistema Mapuche.
 *
 * Este modelo proporciona acceso a los datos de los organismos, incluyendo su código, descripción y organismo superior.
 * También permite acceder a las dependencias y dependencias evaluadas del organismo, así como a los organismos subordinados.
 */
class Dhe4 extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'mapuche.dhe4';
    protected $primaryKey = 'cod_organismo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'cod_organismo',
        'descripcion',
        'cod_organismo_sup',
    ];

    /**
     * Relación con el modelo Dh36 (dependencias).
     */
    public function dependencias(): HasMany
    {
        return $this->hasMany(Dh36::class, 'cod_organismo', 'cod_organismo');
    }

    /**
     * Relación con el modelo Dh36 (dependencias evaluadas).
     */
    public function dependenciasEvaluadas(): HasMany
    {
        return $this->hasMany(Dh36::class, 'cod_organismo_eval', 'cod_organismo');
    }

    /**
     * Relación consigo mismo para el organismo superior.
     */
    public function organismoSuperior(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo_sup', 'cod_organismo');
    }

    /**
     * Relación consigo mismo para los organismos subordinados.
     */
    public function organismosSubordinados(): HasMany
    {
        return $this->hasMany(Dhe4::class, 'cod_organismo_sup', 'cod_organismo');
    }

    public function dh36(): HasMany
    {
        return $this->hasMany(Dh36::class, 'cod_organismo', 'cod_organismo');
    }
}
