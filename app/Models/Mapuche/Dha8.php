<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Data\Mapuche\Dha8Data;
use App\Traits\Mapuche\Dha8Queries;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla de datos personales DGI.
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
    use HasFactory;
    use MapucheConnectionTrait;
    use Dha8Queries;

    /**
     * Deshabilitar timestamps de Laravel.
     */
    public $timestamps = false;

    /**
     * Esquema y nombre de tabla.
     */
    protected $table = 'mapuche.dha8';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'nro_legajo';

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'codigosituacion',
        'codigocondicion',
        'codigoactividad',
        'codigozona',
        'porcaporteadicss',
        'codigomodalcontrat',
        'provincialocalidad',
    ];

    /**
     * Convierte el modelo a un DTO.
     */
    public function toData(): Dha8Data
    {
        return Dha8Data::from($this);
    }

    /**
     * Casteos de atributos.
     */
    protected function casts(): array
    {
        return [
            'nro_legajo' => 'integer',
            'codigosituacion' => 'integer',
            'codigocondicion' => 'integer',
            'codigoactividad' => 'integer',
            'codigozona' => 'integer',
            'porcaporteadicss' => 'float',
            'codigomodalcontrat' => 'integer',
            'provincialocalidad' => 'string',
        ];
    }
}
