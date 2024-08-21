<?php

namespace App\Models\Mapuche\Catalogo;

use App\Models\Dh01;
use App\Models\Mapuche\Catalogo\Dh30;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * Representa un modelo de la tabla 'mapuche.dh08' en la base de datos PostgreSQL.
 *
 * Esta clase proporciona una interfaz para interactuar con los datos de la tabla 'dh08',
 * que contiene información sobre los números de legajo y nacionalidad de los registros.
 *
 * @property int $nro_legaj
 * @property int $nro_tab03
 * @property string $codc_nacio
 * @property bool $nacio_principal
 *
 * @property-read \App\Models\Mapuche\Catalogo\Dh30 $dh30
 * @property-read \App\Models\Dh01 $dh01
 */
class Dh08 extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'dh08';
    public $timestamps = false;
    protected $primaryKey = ['nro_legaj', 'codc_nacio'];
    public $incrementing = false;

    protected $fillable = [
        'nro_legaj',
        'nro_tab03',
        'codc_nacio',
        'nacio_principal',
    ];

    protected $casts = [
        'nro_legaj' => 'integer',
        'nro_tab03' => 'integer',
        'codc_nacio' => 'string',
        'nacio_principal' => 'boolean',
    ];

    public function getKeyName()
    {
        return ['nro_legaj', 'codc_nacio'];
    }

    public function getIncrementing()
    {
        return false;
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('nro_legaj', $this->getAttribute('nro_legaj'))
                     ->where('codc_nacio', $this->getAttribute('codc_nacio'));
    }


    public function dh01(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    public function dh30()
    {
        // return $this->belongsTo(Dh30::class, ['nro_tab03', 'codc_nacio'], ['nro_tabla', 'desc_abrev']);
        return $this->hasOne(Dh30::class, 'nro_tabla', 'nro_tab03')
                    ->where('desc_abrev', $this->codc_nacio);
    }
}
