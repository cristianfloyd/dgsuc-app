<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

/**
 * Modelo para la tabla de Acumuladores del sistema Mapuche
 *
 * @property int $nro_acumu Número de Acumulador (Primary Key)
 * @property string|null $observacion Observaciones del campo (200)
 * @property string|null $desc_acumu Descripción Acumulador (15)
 */
class Dh14 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'dh14';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'nro_acumu';

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nro_acumu',
        'observacion',
        'desc_acumu'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'nro_acumu' => 'integer',
        'observacion' => 'string',
        'desc_acumu' => 'string'
    ];

    /**
     * Obtiene los conceptos (Dh12) que utilizan este acumulador.
     * La relación se establece a través del campo flag_acumu de Dh12
     */
    public function conceptos()
    {
        return Dh12::where('flag_acumu', 'like', DB::raw("concat(repeat('_', ? - 1), 'S', '%')", [$this->nro_acumu]));
    }
}
