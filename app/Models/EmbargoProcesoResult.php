<?php

namespace App\Models;

use Sushi\Sushi;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


/**
 * @method static hydrate(array $results)
 * @method static collection(array $array)
 */
class EmbargoProcesoResult extends Model
{
    use MapucheConnectionTrait;


    // Deshabilitar timestamps ya que no son parte del resultado de la consulta
    public $timestamps = false;


    // Definir el nombre de la tabla (opcional, ya que no es una tabla de base de datos)
    protected $table = 'suc.embargo_proceso_results';

    // Definir los atributos rellenables basados en las columnas del resultado de la consulta
    protected $fillable = [
        'nro_liqui',
        'tipo_embargo',
        'nro_legaj',
        'remunerativo',
        'no_remunerativo',
        'total',
        'codn_conce',
    ];

    // Definir la conversión de atributos para los tipos de datos apropiados
    protected $casts = [
        'nro_liqui' => 'integer',
        'tipo_embargo' => 'integer',
        'nro_legaj' => 'integer',
        'remunerativo' => 'decimal:2',
        'no_remunerativo' => 'decimal:2',
        'total' => 'decimal:2',
        'codn_conce' => 'integer',
    ];



    /**
     * Obtiene una consulta vacía
     *
     * @return Builder
     */
    public function getEmptyQuery(): Builder
    {
        return self::query()->whereRaw('1=0');
    }

    /**
     * Obtiene el monto total
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->remunerativo + $this->no_remunerativo;
    }

    /**
     * Verifica si es embargo bruto
     *
     * @return bool
     */
    public function isBrutoEmbargo(): bool
    {
        return $this->tipo_embargo === 265;
    }
}
