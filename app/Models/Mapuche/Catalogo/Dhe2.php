<?php

namespace App\Models\Mapuche\Catalogo;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dhe2 extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'dhe2';

    protected $primaryKey = ['nro_tabla', 'desc_abrev'];

    protected $fillable = [
        'nro_tabla',
        'desc_abrev',
        'cod_organismo',
    ];

    #[\Override]
    public function getKeyName(): array
    {
        return ['nro_tabla', 'desc_abrev'];
    }

    #[\Override]
    public function getIncrementing(): false
    {
        return false;
    }

    /**
     * Relación con la tabla dh30.
     */
    public function dh30(): BelongsTo
    {
        return $this->belongsTo(Dh30::class, 'nro_tabla', 'nro_tabla')
            ->where('nro_tabla', 'desc__abrev');
    }

    /**
     * Relación con la tabla dhe4.
     */
    public function dhe4(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo', 'cod_organismo');
    }

    #[\Override]
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('nro_tabla', $this->getAttribute('nro_tabla'))
            ->where('desc_abrev', $this->getAttribute('desc_abrev'));
    }

    protected function casts(): array
    {
        return [
            'nro_tabla' => 'integer',
            'desc_abrev' => 'string',
            'cod_organismo' => 'integer',
        ];
    }
}
