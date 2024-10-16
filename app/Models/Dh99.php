<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

// (D) Variable Global: Período Corriente

/**
 * @method static first()
 */
class Dh99 extends Model
{
    use MapucheConnectionTrait;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dh99';



    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * La clave primaria compuesta asociada con la tabla.
     *
     * @var array
     */
    protected $primaryKey = ['per_anoct', 'per_mesct'];

    /**
     * Indica si la clave primaria es auto-incrementable.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'per_anoct',  // Año del período corriente
        'per_mesct',  // Mes del período corriente
        'codc_uacad',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'per_anoct' => 'integer',
        'per_mesct' => 'integer',
        'codc_uacad' => 'string',
    ];
}
