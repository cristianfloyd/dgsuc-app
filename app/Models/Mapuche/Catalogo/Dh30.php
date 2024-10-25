<?php

namespace App\Models\Mapuche\Catalogo;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'dh30';
    protected $primaryKey = ['nro_tabla', 'desc_abrev'];
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

    public function getKeyName(): array
    {
        return ['nro_tabla', 'desc_abrev'];
    }

    public function getIncrementing(): false
    {
        return false;
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
        return $this->hasMany(related: Dhe2::class, foreignKey: 'nro_tabla', localKey: 'nro_tabla')
            ->where(column: 'desc_abrev', operator: $this->desc_abrev);
    }

    public function dh19(): BelongsToMany
    {
        return $this->belongsToMany(Dh19::class, 'dh30_dh19', 'nro_tabla', 'codc_uacad', 'nro_tabla', 'codc_uacad');
    }

    public function dhe4(): BelongsToMany
    {
        return $this->belongsToMany(related: Dhe4::class, table: 'dhe2', foreignPivotKey: 'nro_tabla', relatedPivotKey: 'cod_organismo', parentKey: 'nro_tabla', relatedKey: 'cod_organismo');
    }

    public function organismos(): BelongsToMany
    {
        return $this->belongsToMany(related: Dhe4::class, table: 'dhe2', foreignPivotKey: 'nro_tabla', relatedPivotKey: 'cod_organismo')
            ->withPivot('desc_abrev');
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('nro_tabla', $this->getAttribute('nro_tabla'))
            ->where('desc_abrev', $this->getAttribute('desc_abrev'));
    }
}
