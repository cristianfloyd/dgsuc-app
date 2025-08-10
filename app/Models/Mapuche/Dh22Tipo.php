<?php

namespace App\Models\Mapuche;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent para la tabla mapuche.dh22_tipos.
 *
 * Esta clase representa los tipos de liquidaciones en el sistema.
 */
class Dh22Tipo extends Model
{
    use MapucheConnectionTrait;

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indica si el ID es auto-incrementable.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indica el nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'dh22_tipos';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     * @phpstan-ignore property.phpDocType
     */
    protected $fillable = [
        'id',
        'desc_corta',
        'desc_larga',
    ];

    /**
     * Obtiene las liquidaciones asociadas a este tipo.
     */
    public function liquidaciones()
    {
        return $this->hasMany(Dh22::class, 'id_tipo_liqui', 'id');
    }
}
