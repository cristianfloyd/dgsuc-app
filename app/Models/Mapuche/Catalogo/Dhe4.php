<?php

namespace App\Models\Mapuche\Catalogo;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para la tabla 'mapuche.dhe4' que representa un organismo dentro del sistema Mapuche.
 *
 * Este modelo proporciona acceso a los datos de los organismos, incluyendo su código, descripción y organismo superior.
 * También permite acceder a las dependencias y dependencias evaluadas del organismo, así como a los organismos subordinados.
 */
class Dhe4 extends Model
{
    use MapucheConnectionTrait;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'dhe4';

    protected $primaryKey = 'cod_organismo';

    protected $fillable = [
        'cod_organismo',
        'descripcion',
        'cod_organismo_sup',
    ];

    /**
     * Relación con el modelo Dh36 (dependencias).
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapuche\Catalogo\Dh36, $this>
     */
    public function dependencias(): HasMany
    {
        return $this->hasMany(Dh36::class, 'cod_organismo', 'cod_organismo');
    }

    /**
     * Relación con el modelo Dh36 (dependencias evaluadas).
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapuche\Catalogo\Dh36, $this>
     */
    public function dependenciasEvaluadas(): HasMany
    {
        return $this->hasMany(Dh36::class, 'cod_organismo_eval', 'cod_organismo');
    }

    /**
     * Relación consigo mismo para el organismo superior.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Mapuche\Catalogo\Dhe4, $this>
     */
    public function organismoSuperior(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo_sup', 'cod_organismo');
    }

    /**
     * Relación consigo mismo para los organismos subordinados.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapuche\Catalogo\Dhe4, $this>
     */
    public function organismosSubordinados(): HasMany
    {
        return $this->hasMany(Dhe4::class, 'cod_organismo_sup', 'cod_organismo');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapuche\Catalogo\Dh36, $this>
     */
    public function dh36(): HasMany
    {
        return $this->hasMany(Dh36::class, 'cod_organismo', 'cod_organismo');
    }

    /**
     * Relación de uno a muchos entre el modelo Dhe4 y el modelo Dhe2, donde cada Dhe4 puede tener múltiples Dhe2.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapuche\Catalogo\Dhe2, $this>
     */
    public function dhe2(): HasMany
    {
        return $this->hasMany(Dhe2::class, 'cod_organismo', 'cod_organismo');
    }

    /**
     * Relación de muchos a muchos entre el modelo Dhe4 y el modelo Dh30 a través de la tabla pivote dhe2.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Mapuche\Catalogo\Dh30, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function dh30Items(): BelongsToMany
    {
        return $this->belongsToMany(related: Dh30::class, table: 'dhe2', foreignPivotKey: 'cod_organismo', relatedPivotKey: 'nro_tabla')
            ->withPivot('desc_abrev');
    }
}
