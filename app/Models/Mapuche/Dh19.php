<?php

namespace App\Models;

use App\Models\Mapuche\Catalogo\Dh30;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh19 extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'mapuche.dh19';


    protected $primaryKey = ['nro_legaj', 'codn_conce', 'tipo_docum', 'nro_docum'];


    public $incrementing = false;


    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
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
     * Especifica el tipo de datos que se utilizarán para los campos de la tabla 'mapuche.dh19'.
     * Esto permite que Laravel realice la conversión automática de los datos al guardarlos o recuperarlos de la base de datos.
     */
    protected $casts = [
        'nro_legaj' => 'integer',
        'codn_conce' => 'integer',
        'nro_tabla' => 'integer',
        'nro_docum' => 'integer',
        'porc_benef' => 'decimal:2',
    ];


    /**
     * Obtiene la relación Dh12 que pertenece a la instancia actual de Dh19.
     * La relación se filtra por el valor de `codn_conce` de la instancia actual.
     *
     * @return BelongsTo
     */
    public function dh12(): BelongsTo
    {
        return $this->belongsTo(Dh12::class, 'codn_conce', 'codn_conce');
    }

    /**
     * Obtiene la relación Dh30 que pertenece a la instancia actual de Dh19.
     * La relación se filtra por el valor de `tipo_docum` y `desc_abrev` de la instancia actual.
     *
     * @return BelongsTo
     */
    public function dh30(): BelongsTo
    {
        return $this->belongsTo(Dh30::class, 'nro_tabla', 'nro_tabla')
            ->where('tipo_docum', $this->tipo_docum)
            ->where('desc_abrev', $this->tipo_docum);
    }
}
