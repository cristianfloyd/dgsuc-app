<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class AfipArt
 *
 * @package App\Models
 * @property string $cuil_formateado
 * @property string $cuil_original
 * @property string $apellido_y_nombre
 * @property \Illuminate\Support\Carbon $nacimiento
 * @property string $sueldo
 * @property string $sexo
 * @property int $nro_legaj
 * @property string $establecimiento
 * @property string $tarea
 * @property int $concepto
 */
class AfipArt extends Model
{
    use HasFactory;

    /**
     * Especifica la conexión a la base de datos si no es la predeterminada
     *
     * @var string
     */
    protected $connection = 'pgsql-suc';

    /**
     * Especifica la tabla asociada al modelo
     *
     * @var string
     */
    protected $table = 'suc.afip_art';

    /**
     * Especifica la clave primaria de la tabla
     *
     * @var string
     */
    protected $primaryKey = 'cuil_original';

    /**
     * Indica que la clave primaria no es un incremento automático
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Especifica el tipo de clave primaria
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Desactiva las marcas de tiempo automáticas (created_at, updated_at)
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define los atributos que se pueden asignar en masa
     *
     * @var array
     */
    protected $fillable = [
        'cuil_formateado',
        'cuil_original',
        'apellido_y_nombre',
        'nacimiento',
        'sueldo',
        'sexo',
        'nro_legaj',
        'establecimiento',
        'tarea',
        'concepto',
    ];
}
