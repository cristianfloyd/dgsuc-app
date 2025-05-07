<?php

declare(strict_types=1);

namespace App\Services\Afip;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;

class SicossCpto205Service
{
    use MapucheConnectionTrait;

    /**
     * Actualiza datos relacionados con el concepto 205 en SICOSS
     *
     * Este método crea una tabla temporal con información de agentes que
     * tienen el concepto 789, calculando el monto como un porcentaje (50%)
     * del total de dicho concepto, pero solo para aquellos que también tienen
     * el concepto 205 y están en la tabla de control_aportes_diferencias.
     *
     * @param array $params Parámetros adicionales (opcional)
     * @return array Resultado de la operación
     */
    public function actualizarCpto205(array $params = []): array
    {
        // Liquidaciones por defecto: definidas en la consulta original
        $liquidaciones = $params['liquidaciones'] ?? [21, 24, 25, 26, 27];

        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            // Limpiar tabla temporal si existe
            DB::connection($this->getConnectionName())->statement("DROP TABLE IF EXISTS tcpto205");

            // Crear tabla temporal con los datos calculados
            $query = "
                SELECT d21.nro_legaj, c.cuil, ((SUM(d21.impp_conce) * 100) / 2)::NUMERIC(10, 2) AS monto
                INTO TEMP tcpto205
                FROM mapuche.dh21 d21,
                    mapuche.vdh01 c
                WHERE d21.nro_liqui IN (" . implode(',', $liquidaciones) . ")
                    AND d21.nro_legaj = c.nro_legaj
                    AND d21.codn_conce = 789
                    AND d21.nro_legaj IN
                        (SELECT DISTINCT nro_legaj FROM mapuche.dh21 WHERE nro_liqui IN (" . implode(',', $liquidaciones) . ") AND codn_conce = '205')
                    AND d21.nro_legaj IN (SELECT b.nro_legaj
                                            FROM suc.control_aportes_diferencias a,
                                                mapuche.vdh01 b
                                            WHERE a.cuil = b.cuil)
                GROUP BY d21.nro_legaj, c.cuil
                ORDER BY nro_legaj
            ";

            DB::connection($this->getConnectionName())->statement($query);

            // Obtener cantidad de registros afectados
            $registrosCount = DB::connection($this->getConnectionName())->selectOne("SELECT COUNT(*) as total FROM tcpto205");
            $totalRegistros = $registrosCount->total;

            // Puedes agregar aquí cualquier actualización adicional usando esta tabla temporal
            // Por ejemplo, actualizar alguna tabla de SICOSS con estos datos

            // DB::commit();
            DB::connection($this->getConnectionName())->rollBack();  // Descomentar para probar

            return [
                'status' => 'success',
                'message' => "Se procesaron $totalRegistros registros para el concepto 205",
                'data' => [
                    'registros_procesados' => $totalRegistros,
                    'liquidaciones' => $liquidaciones
                ]
            ];

        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();

            Log::error('Error en actualización de concepto 205', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
