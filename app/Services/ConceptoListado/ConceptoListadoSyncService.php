<?php

namespace App\Services\ConceptoListado;

use App\Models\Reportes\ConceptoListado;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConceptoListadoSyncService implements ConceptoListadoServiceInterface
{
    use MapucheConnectionTrait;

    private PeriodoFiscalService $periodoFiscalService;

    public function __construct(
        private ConceptoListado $conceptoListado,
        PeriodoFiscalService $periodoFiscalService,
    ) {
        $this->periodoFiscalService = $periodoFiscalService;
    }

    /**
     * Sincroniza los datos desde Mapuche a la tabla de reportes.
     *
     * @param string|null $periodoFiscal Período fiscal en formato 'YYYY-MM' (opcional)
     * @param int|null $nroLiqui Número de liquidación específico (opcional)
     *
     * @throws \Exception
     *
     * @return int Número de registros insertados
     */
    public function sync(?string $periodoFiscal = null, ?int $nroLiqui = null): int
    {
        $connection = $this->conceptoListado::getMapucheConnection();

        try {
            DB::beginTransaction();

            // Limpiamos la tabla antes de insertar nuevos datos
            DB::connection($connection->getName())
                ->table('suc.rep_concepto_listado')
                ->truncate();

            // Determinamos qué tabla usar basado en el período fiscal
            $tablaOrigen = $this->determinarTablaOrigen($periodoFiscal);

            // Construimos la consulta de inserción
            $query = $this->buildSyncQuery($tablaOrigen, $periodoFiscal, $nroLiqui);

            // Ejecutamos la consulta y obtenemos el número de filas afectadas
            $affectedRows = DB::connection($connection->getName())
                ->affectingStatement($query);

            DB::commit();

            // Limpiamos el caché después de actualizar los datos
            $this->clearCache();

            $periodoLog = $periodoFiscal ? " para el período $periodoFiscal" : '';
            $liquidacionLog = $nroLiqui ? " y liquidación #$nroLiqui" : '';

            Log::info("Tabla suc.rep_concepto_listado sincronizada exitosamente$periodoLog$liquidacionLog", [
                'registros_insertados' => $affectedRows,
                'tabla_origen' => $tablaOrigen,
            ]);

            return $affectedRows;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en sincronización de conceptos listado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'periodo_fiscal' => $periodoFiscal,
                'nro_liqui' => $nroLiqui,
            ]);
            throw $e;
        }
    }

    // Implementación de los métodos de la interfaz
    public function getConnectionName(): string
    {
        return $this->getConnectionFromTrait()->getName();
    }

    public function getConnection()
    {
        return $this->getConnectionFromTrait();
    }

    /**
     * Determina qué tabla usar basado en el período fiscal.
     *
     * @param string|null $periodoFiscal Período fiscal en formato 'YYYY-MM'
     *
     * @return string Nombre de la tabla a utilizar
     */
    private function determinarTablaOrigen(?string $periodoFiscal = null): string
    {
        // Si no se especifica un período, usamos la tabla histórica por defecto
        if (!$periodoFiscal) {
            return 'mapuche.dh21h';
        }

        // Obtenemos el período actual de la base de datos
        $periodoActual = $this->periodoFiscalService->getPeriodoFiscalFromDatabase();
        $periodoActualStr = "{$periodoActual['year']}-{$periodoActual['month']}";

        // Comparamos el período solicitado con el actual
        if ($periodoFiscal === $periodoActualStr) {
            return 'mapuche.dh21'; // Tabla actual
        }
        return 'mapuche.dh21h'; // Tabla histórica
    }

    /**
     * Construye la consulta SQL para la sincronización.
     *
     * @param string $tablaOrigen Tabla de origen (dh21 o dh21h)
     * @param string|null $periodoFiscal Período fiscal en formato 'YYYY-MM'
     * @param int|null $nroLiqui Número de liquidación específico
     *
     * @return string Consulta SQL
     */
    private function buildSyncQuery(string $tablaOrigen, ?string $periodoFiscal = null, ?int $nroLiqui = null): string
    {
        // Preparamos las condiciones adicionales
        $condicionesPeriodo = '';

        if ($periodoFiscal) {
            [$year, $month] = explode('-', $periodoFiscal);
            $condicionesPeriodo .= " AND dh22.per_liano = $year AND dh22.per_limes = $month";
        }

        if ($nroLiqui) {
            $condicionesPeriodo .= " AND d.nro_liqui = $nroLiqui";
        }

        return "
            INSERT INTO suc.rep_concepto_listado (
                id, nro_liqui, desc_liqui, periodo_fiscal,
                nro_legaj, nro_cargo, apellido, nombre,
                cuil, codc_uacad, codn_conce, impp_conce
            )
            SELECT DISTINCT
                ROW_NUMBER() OVER () AS id,
                d.nro_liqui,
                dh22.desc_liqui,
                CONCAT(dh22.per_liano, LPAD(dh22.per_limes::TEXT, 2, '0'::TEXT)) AS periodo_fiscal,
                d.nro_legaj,
                d.nro_cargo,
                dh01.desc_appat AS apellido,
                dh01.desc_nombr AS nombre,
                CONCAT(dh01.nro_cuil1, lpad(dh01.nro_cuil::text, '8','0'), dh01.nro_cuil2) AS cuil,
                d.codc_uacad,
                d.codn_conce,
                d.impp_conce
            FROM $tablaOrigen d
            LEFT JOIN mapuche.dh01 ON d.nro_legaj = dh01.nro_legaj
            JOIN mapuche.dh22 ON d.nro_liqui = dh22.nro_liqui
            WHERE d.codn_conce/100 IN (1,2,3,4)
            $condicionesPeriodo
        ";
    }

    /**
     * Limpia el caché relacionado.
     */
    private function clearCache(): void
    {
        Cache::tags(['rep_concepto_listado'])->flush();
    }
}
