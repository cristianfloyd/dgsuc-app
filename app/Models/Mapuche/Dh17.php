<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo para la tabla de Objetos del Gasto x Concepto
 *
 * @property int $codn_conce Nro. de Concepto (Primary Key)
 * @property string|null $objt_gtope Objeto del Gasto Personal Permanente
 * @property string|null $objt_gtote Objeto del Gasto Personal Temporario
 * @property int|null $nro_prove Nro. de Proveedor
 */
class Dh17 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'mapuche.dh17';

    /**
     * Clave primaria de la tabla
     */
    protected $primaryKey = 'codn_conce';

    /**
     * Indica si el modelo debe tener timestamps
     */
    public $timestamps = false;

    /**
     * Atributos que son asignables masivamente
     */
    protected $fillable = [
        'codn_conce',
        'objt_gtope',
        'objt_gtote',
        'nro_prove'
    ];

    /**
     * Casteos de atributos
     */
    protected $casts = [
        'codn_conce' => 'integer',
        'objt_gtope' => 'string',
        'objt_gtote' => 'string',
        'nro_prove' => 'integer'
    ];
}
