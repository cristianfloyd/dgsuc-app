<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Data\Mapuche\Dha8Data;
use App\Traits\Mapuche\Dha8Queries;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo para la tabla de datos personales DGI
 *
 * @property int $nro_legajo Número de Legajo (Primary Key)
 * @property int|null $codigosituacion Código de Situación
 * @property int|null $codigocondicion Código de Condición
 * @property int|null $codigoactividad Código de Actividad
 * @property int|null $codigozona Código de Zona
 * @property float|null $porcaporteadicss Porcentaje Aporte Adicional SS
 * @property int|null $codigomodalcontrat Código Modalidad de Contratación
 * @property string|null $provincialocalidad Provincia/Localidad
 */
class Dha8 extends Model
{
    use HasFactory, MapucheConnectionTrait;
    use Dha8Queries;


    /**
     * Esquema y nombre de tabla
     */
    protected $table = 'mapuche.dha8';

    /**
     * Clave primaria
     */
    protected $primaryKey = 'nro_legajo';

    /**
     * Deshabilitar timestamps de Laravel
     */
    public $timestamps = false;

    /**
     * Atributos asignables masivamente
     */
    protected $fillable = [
        'codigosituacion',
        'codigocondicion',
        'codigoactividad',
        'codigozona',
        'porcaporteadicss',
        'codigomodalcontrat',
        'provincialocalidad'
    ];

    /**
     * Casteos de atributos
     */
    protected $casts = [
        'nro_legajo' => 'integer',
        'codigosituacion' => 'integer',
        'codigocondicion' => 'integer',
        'codigoactividad' => 'integer',
        'codigozona' => 'integer',
        'porcaporteadicss' => 'float',
        'codigomodalcontrat' => 'integer',
        'provincialocalidad' => 'string'
    ];

    /**
     * Convierte el modelo a un DTO
     */
    public function toData(): Dha8Data
    {
        return Dha8Data::from($this);
    }
}
