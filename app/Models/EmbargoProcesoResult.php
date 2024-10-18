<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sushi\Sushi;


/**
 * @method static hydrate(array $results)
 * @method static collection(array $array)
 */
class EmbargoProcesoResult extends Model
{
    use Sushi;

    public $timestamps = false;

    // Deshabilitar timestamps ya que no son parte del resultado de la consulta
    protected $connection = 'pgsql-mapuche';

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
     * Actualiza los datos del proceso de embargo y limpia el caché de Sushi.
     *
     * @param array $nroComplementarias Números de complementarias a procesar.
     * @param int $nroLiquiDefinitiva Número de liquidación definitiva.
     * @param int $nroLiquiProxima Número de próxima liquidación.
     * @param bool $insertIntoDh25 Indica si se debe insertar en la tabla DH25.
     */
    public static function updateData(array $nroComplementarias, int $nroLiquiDefinitiva, int $nroLiquiProxima, bool $insertIntoDh25 = false): Builder
    {
        $results = self::executeEmbargoProcesoQuery($nroComplementarias, $nroLiquiDefinitiva, $nroLiquiProxima, $insertIntoDh25);

        self::resetSushiCache();
        return $results;
    }

    /**
     * Ejecuta la consulta del proceso de embargo y devuelve los resultados como una colección de modelos EmbargoProcesoResult.
     *
     * @param array $nroComplementarias
     * @param int $nroLiquiDefinitiva
     * @param int $nroLiquiProxima
     * @param bool $insertIntoDh25
     * @return Builder
     */
    public static function executeEmbargoProcesoQuery(
        array $nroComplementarias,
        int   $nroLiquiDefinitiva,
        int   $nroLiquiProxima,
        bool  $insertIntoDh25 = false
    ): Builder
    {
        $arrayString = empty($nroComplementarias)
            ? 'ARRAY[]::integer[]'
            : 'ARRAY[' . implode(',', array_map('intval', $nroComplementarias)) . ']';

        $results = DB::connection('pgsql-suc')->select("SELECT * FROM suc.emb_proceso( $arrayString, ?, ?, ?)", [
            // $arrayString,
            $nroLiquiDefinitiva,
            $nroLiquiProxima,
            $insertIntoDh25
        ]);

        if (empty($results)) {
            return self::getEmptyQuery();
        }

        return self::hydrate($results)->toQuery();
    }

    /**
     * Obtiene una instancia de Builder vacía para la consulta del proceso de embargo.
     *
     * @return Builder
     */
    public static function getEmptyQuery(): Builder
    {
        return self::query()->whereRaw('1=0');
    }

    public static function resetSushiCache(): void
    {
        $instance = new static;
        $cacheKey = $instance->getSushiCacheKey();
        cache()->forget($cacheKey);
    }

    protected function getSushiCacheKey(): string
    {
        return 'sushi.' . $this->getTable() . '.rows';
    }

    public function getRows(): array
    {
        // return $this->getEmptyQuery()->toArray();
        return [];
    }

    //Metodo para obtener los datos para filamentPHP

    public function getDataForFilament()
    {
        return self::all();
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
