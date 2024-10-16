<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


/**
 * @method static hydrate(array $results)
 * @method static collection(array $array)
 */
class EmbargoProcesoResult extends Model
{

    // Deshabilitar timestamps ya que no son parte del resultado de la consulta
    public $timestamps = false;

    // Definir el nombre de la tabla (opcional, ya que no es una tabla de base de datos)
    protected $table = 'embargo_proceso_results';

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
    protected mixed $remunerativo;
    protected mixed $tipo_embargo;
    protected mixed $no_remunerativo;

    /**
     * Ejecuta la consulta del proceso de embargo y devuelve los resultados como una colección de modelos EmbargoProcesoResult.
     *
     * @param array $nroComplementarias
     * @param int $nroLiquiDefinitiva
     * @param int $nroLiquiProxima
     * @param bool $insertIntoDh25
     * @return mixed
     */
    public static function executeEmbargoProcesoQuery(
        array $nroComplementarias,
        int   $nroLiquiDefinitiva,
        int   $nroLiquiProxima,
        bool  $insertIntoDh25 = false
    ): Builder
    {
        $results = DB::select('SELECT * FROM suc.emb_proceso(?, ?, ?, ?)', [
            $nroComplementarias,
            $nroLiquiDefinitiva,
            $nroLiquiProxima,
            $insertIntoDh25
        ]);

        return self::hydrate($results)->toQuery();
    }

    /**
     * Obtiene el monto total para conceptos remunerativos y no remunerativos.
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->remunerativo + $this->no_remunerativo;
    }

    /**
     * Verifica si el embargo es de tipo 'bruto' (265).
     *
     * @return bool
     */
    public function isBrutoEmbargo(): bool
    {
        return $this->tipo_embargo === 265;
    }

    /**
     * Obtiene la descripción del tipo de embargo.
     *
     * @return string
     */
    public function getEmbargoTypeDescription(): string
    {
        return $this->tipo_embargo === 265 ? 'Bruto' : 'Neto';
    }
}
