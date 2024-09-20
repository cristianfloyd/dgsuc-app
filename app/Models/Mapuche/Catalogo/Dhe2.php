<?php

namespace App\Models\Mapuche;

use App\Models\Mapuche\Catalogo\Dh30;
use App\Models\Mapuche\Catalogo\Dhe4;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dhe2 extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'dhe2';
    public $timestamps = false;
    protected $primaryKey = ['nro_tabla', 'desc_abrev'];
    public $incrementing = false;

    protected $fillable = [
        'nro_tabla',
        'desc_abrev',
        'cod_organismo',
    ];

    protected $casts = [
        'nro_tabla' => 'integer',
        'desc_abrev' => 'string',
        'cod_organismo' => 'integer',
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

    /**
     * Relación con la tabla dh30.
     *
     * @return BelongsTo
     */
    public function dh30(): BelongsTo
    {
        return $this->belongsTo(Dh30::class, 'nro_tabla', 'nro_tabla')
            ->where('nro_tabla', 'desc__abrev');;
    }

    /**
     * Relación con la tabla dhe4.
     *
     * @return BelongsTo
     */
    public function dhe4(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo', 'cod_organismo');
    }

}
