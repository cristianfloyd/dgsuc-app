<?php

namespace App\Models\Mapuche;

use App\Models\Dh12;
use App\Models\Mapuche\Catalogo\Dh30;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh19 extends Model
{
    use MapucheConnectionTrait;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'mapuche.dh19';

    // @phpstan-ignore property.defaultValue
    protected $primaryKey = ['nro_legaj', 'codn_conce', 'tipo_docum', 'nro_docum'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     * @phpstan-ignore property.phpDocType
     */
    protected $fillable = [
        'nro_legaj',
        'codn_conce',
        'nro_tabla',
        'tipo_docum',
        'nro_docum',
        'desc_apell',
        'desc_nombre',
        'porc_benef',
    ];

    /**
     * Obtiene la relación Dh12 que pertenece a la instancia actual de Dh19.
     * La relación se filtra por el valor de `codn_conce` de la instancia actual.
     */
    public function dh12(): BelongsTo
    {
        return $this->belongsTo(Dh12::class, 'codn_conce', 'codn_conce');
    }

    /**
     * Obtiene la relación Dh30 que pertenece a la instancia actual de Dh19.
     * La relación se filtra por el valor de `tipo_docum` y `desc_abrev` de la instancia actual.
     */
    public function dh30(): BelongsTo
    {
        return $this->belongsTo(Dh30::class, 'nro_tabla', 'nro_tabla')
            ->where('tipo_docum', $this->tipo_docum)
            ->where('desc_abrev', $this->tipo_docum);
    }

    /**
     * Especifica el tipo de datos que se utilizarán para los campos de la tabla 'mapuche.dh19'.
     * Esto permite que Laravel realice la conversión automática de los datos al guardarlos o recuperarlos de la base de datos.
     */
    protected function casts(): array
    {
        return [
            'nro_legaj' => 'integer',
            'codn_conce' => 'integer',
            'nro_tabla' => 'integer',
            'nro_docum' => 'integer',
            'porc_benef' => 'decimal:2',
        ];
    }
}
