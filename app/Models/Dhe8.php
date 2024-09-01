<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Dhe8
 *
 * @package App\Models
 * @property string $codigogradooa
 * @property string|null $descgradooa
 */
class Dhe8 extends Model
{
    use HasFactory;

    /**
     * Especifica la conexión a la base de datos si no es la predeterminada
     *
     * @var string
     */
    protected $connection = 'pgsql';

    /**
     * Especifica la tabla asociada al modelo
     *
     * @var string
     */
    protected $table = 'mapuche.dhe8';

    /**
     * Especifica la clave primaria de la tabla
     *
     * @var string
     */
    protected $primaryKey = 'codigogradooa';

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
        'codigogradooa',
        'descgradooa',
    ];
}
