<?php

namespace App\Models;

use Sushi\Sushi;
use App\Traits\MapucheSchemaSuc;
use Illuminate\Support\Collection;
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
    use Sushi;

    protected $connection = 'pgsql-mapuche';

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
     * @return Builder
     */
    public static function executeEmbargoProcesoQuery(
        array $nroComplementarias,
        int   $nroLiquiDefinitiva,
        int   $nroLiquiProxima,
        bool  $insertIntoDh25 = false
    ): Builder {
        $arrayString = 'ARRAY[' . implode(',', array_map('intval', $nroComplementarias)) . ']';

        $results = DB::connection('pgsql-suc')->select("SELECT * FROM suc.emb_proceso( $arrayString, ?, ?, ?)", [
            // $arrayString,
            $nroLiquiDefinitiva,
            $nroLiquiProxima,
            $insertIntoDh25
        ]);


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

    public function getRows(): array
    {
        // return $this->getEmptyQuery()->toArray();
        return [];
    }

    /**
     * Actualiza los datos del proceso de embargo y limpia el caché de Sushi.
     *
     * @param array $nroComplementarias Números de complementarias a procesar.
     * @param int $nroLiquiDefinitiva Número de liquidación definitiva.
     * @param int $nroLiquiProxima Número de próxima liquidación.
     * @param bool $insertIntoDh25 Indica si se debe insertar en la tabla DH25.
     * @return mixed Resultados del proceso de embargo.
     */
    public static function updateData(array $nroComplementarias, int $nroLiquiDefinitiva, int $nroLiquiProxima, bool $insertIntoDh25 = false)
    {
        $results = self::executeEmbargoProcesoQuery($nroComplementarias, $nroLiquiDefinitiva, $nroLiquiProxima, $insertIntoDh25);
        self::resetSushiCache();
        return $results;
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
