<?php

namespace App\Models\Mapuche\Catalogo;

use App\Models\Dh03;
use App\Models\Mapuche\Dhe2;
use App\Models\Mapuche\Catalogo\Dh08;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Representa un modelo de la tabla 'mapuche.dh30' en la base de datos.
 * Esta tabla contiene información sobre los ítems de una tabla específica.
 * Tabla multiple de abreviaturas.
 *
 * @property int $nro_tabla
 * @property string $desc_abrev
 * @property string $desc_item
 *
 * @method HasMany dh08()
 *     Obtiene una colección de modelos Dh08 relacionados con este modelo.
 *
 * @method HasMany dh03()
 *     Obtiene una colección de modelos Dh03 relacionados con este modelo.
 */
class Dh30 extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'dh30';
    public $timestamps = false;
    protected $primaryKey = ['nro_tabla', 'desc_abrev'];
    public $incrementing = false;

    protected $fillable = [
        'nro_tabla',
        'desc_abrev',
        'desc_item',
    ];

    protected $casts = [
        'nro_tabla' => 'integer',
        'desc_abrev' => 'string',
        'desc_item' => 'string',
    ];

    public function getKeyName()
    {
        return ['nro_tabla', 'desc_abrev'];
    }

    public function getIncrementing()
    {
        return false;
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('nro_tabla', $this->getAttribute('nro_tabla'))
                    ->where('desc_abrev', $this->getAttribute('desc_abrev'));
    }

    public function dh08(): HasMany
    {
        // return $this->hasMany(Dh08::class, ['nro_tabla', 'desc_abrev'], ['nro_tabla', 'desc_abrev']);
        return $this->hasMany(Dh08::class, 'nro_tabla', 'nro_tabla')
            ->where('desc_abrev', $this->desc_abrev);
    }

    public function dh03(): HasMany
    {
        return $this->hasMany(Dh03::class, 'codc_uacad',  'desc_abrev');
    }

    public function dhe2(): HasMany
    {
        return $this->hasMany(Dhe2::class, 'nro_tabla', 'nro_tabla')
            ->where('desc_abrev', $this->desc_abrev);
    }
}
