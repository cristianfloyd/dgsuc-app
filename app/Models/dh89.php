<?php

namespace App\Models;

use App\Models\Dh11;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dh89 extends Model
{
    use MapucheConnectionTrait;

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

    /**
     * Relación con el modelo Dh11
     */
    public function dh11s(): HasMany
    {
        return $this->hasMany(Dh11::class, 'codigoescalafon', 'codigoescalafon');
    }
}
