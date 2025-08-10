<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh92 extends Model
{
    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dh92';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'autonum';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'nrolegajo',
        'codn_banco',
        'codn_sucur',
        'tipo_cuent',
        'nro_cuent',
        'codn_verif',
        'nrovalorpago',
        'cbu',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'nro_cuent' => 'float',
    ];

    /**
     * Obtiene el legajo asociado.
     *
     * @return BelongsTo
     */
    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nrolegajo', 'nro_legaj');
    }

    /**
     * Obtiene el banco asociado.
     *
     * @return BelongsTo
     */
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Dh84::class, 'codn_banco', 'nroentidadbancaria');
    }

    /**
     * Obtiene el valor de pago asociado.
     *
     * @return BelongsTo
     */
    public function valorPago(): BelongsTo
    {
        return $this->belongsTo(Dh91::class, 'nrovalorpago', 'nrovalorpago');
    }

    /**
     * Obtiene la sucursal bancaria asociada.
     *
     * @return BelongsTo
     */
    public function sucursalBancaria(): BelongsTo
    {
        return $this->belongsTo(Dha9::class, 'codn_banco', 'codigo_entbancaria')
            ->where('codigo_sucursal', $this->codn_sucur);
    }
}
