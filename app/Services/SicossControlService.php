<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;

class SicossControlService
{
    use MapucheConnectionTrait;

    protected $connection;

    public function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    protected function getConnection(): string
    {
        if (!$this->connection) {
            throw new \RuntimeException('Debe establecer una conexión antes de ejecutar los controles');
        }
        return $this->connection;
    }

    /**
     * Realiza controles y actualizaciones post-importación
     *
     * @return array Resultados de los controles con la siguiente estructura:
     * [
     *     'success' => bool,
     *     'resultados' => [
     *         'cuils_faltantes' => array,
     *         'registros_actualizados' => int,
     *         'diferencias_encontradas' => array
     *     ]
     * ]
     */
    public function ejecutarControlesPostImportacion(): array
    {
        $resultados = [];

        try {
            DB::connection($this->getConnection())->beginTransaction();

            // 1. Control de CUILs faltantes y materialización de resultados
            $this->materializarControlCuils();
            $cuilsFaltantes = $this->obtenerResultadosControlCuils();
            $resultados['cuils_faltantes'] = $cuilsFaltantes;

            // 2. Actualización de UA/CAD y Carácter
            $actualizados = $this->actualizarUacadCaracter();
            $resultados['registros_actualizados'] = $actualizados;

            // 3. Control y materialización de aportes y contribuciones
            $this->materializarControlAportes();
            $diferencias = $this->obtenerResultadosControlAportes();
            $resultados['diferencias_encontradas'] = $diferencias;

            // 4. Control y materialización de ART
            $this->materializarControlArt();
            $diferenciasArt = $this->obtenerResultadosControlArt();
            $resultados['diferencias_art'] = $diferenciasArt;

            DB::connection($this->getConnection())->commit();

            return [
                'success' => true,
                'resultados' => $resultados
            ];
        } catch (\Exception $e) {
            DB::connection($this->getConnection())->rollBack();
            Log::error('Error en controles post-importación SICOSS: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Materializa los resultados del control de CUILs en una tabla temporal
     *
     * @throws \Exception Si hay error en la creación de la tabla o inserción de datos
     */
    private function materializarControlCuils(): void
    {
        // Primero ejecutamos la consulta
        $resultados = DB::connection($this->getConnection())->select("
            WITH cuils_dh21 AS (
                SELECT DISTINCT
                    (nro_cuil1::CHAR(2) || LPAD(nro_cuil::CHAR(8), 8, '0') || nro_cuil2::CHAR(1)) AS cuil,
                    'DH21' as origen
                FROM mapuche.dh21h
                JOIN mapuche.dh01 ON dh21h.nro_legaj = dh01.nro_legaj
                WHERE nro_liqui IN (2,4,5)
            ),
            cuils_sicoss AS (
                SELECT DISTINCT cuil, 'SICOSS' as origen
                FROM suc.afip_mapuche_sicoss_calculos
            )
            SELECT cuil, origen
            FROM (
                SELECT cuil, origen FROM cuils_dh21
                UNION ALL
                SELECT cuil, origen FROM cuils_sicoss
            ) t
            GROUP BY cuil, origen
            HAVING COUNT(*) = 1
        ");

        // Luego insertamos en la tabla permanente
        foreach ($resultados as $resultado) {
            DB::connection($this->getConnection())->table('suc.control_cuils_diferencias')->insert([
                'cuil' => $resultado->cuil,
                'origen' => $resultado->origen,
                'fecha_control' => now(),
                'connection' => $this->getConnection()
            ]);
        }
    }

    /**
     * Obtiene los resultados del control de CUILs
     *
     * @return array Registros con diferencias de CUILs
     */
    private function obtenerResultadosControlCuils(): array
    {
        return DB::connection($this->getConnection())->select("
            SELECT *
            FROM suc.control_cuils_diferencias
            ORDER BY origen, cuil
        ");
    }

    /**
     * Materializa los resultados del control de aportes en una tabla temporal
     *
     * @throws \Exception Si hay error en la creación de la tabla o inserción de datos
     */
    private function materializarControlAportes(): void
    {
        DB::connection($this->getConnection())->statement("
            DROP TABLE IF EXISTS suc.control_aportes_diferencias;

            CREATE TABLE suc.control_aportes_diferencias AS
            SELECT a.*,
                (aportesijpdh21 + aporteinssjpdh21) - (
                    aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re
                ) AS diferencia
            FROM dh21aporte a
            JOIN suc.afip_mapuche_sicoss_calculos b ON a.cuil = b.cuil
            WHERE abs(
                (
                    (aportesijpdh21 + aporteinssjpdh21) - (
                        aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re
                    )
                )::numeric
            ) > 1
        ");
    }

    /**
     * Obtiene los resultados del control de aportes
     *
     * @return array Registros con diferencias en aportes
     */
    private function obtenerResultadosControlAportes(): array
    {
        return DB::connection($this->getConnection())->select("
            SELECT *
            FROM suc.control_aportes_diferencias
            ORDER BY diferencia DESC
        ");
    }

    /**
     * Materializa los resultados del control de ART en una tabla temporal
     *
     * @throws \Exception Si hay error en la creación de la tabla o inserción de datos
     */
    private function materializarControlArt(): void
    {
        DB::connection($this->getConnection())->statement("
            DROP TABLE IF EXISTS suc.control_art_diferencias;

            CREATE TABLE suc.control_art_diferencias AS
            WITH dh21art AS (
                SELECT dh21h.nro_legaj,
                    (nro_cuil1::CHAR(2) || LPAD(nro_cuil::CHAR(8), 8, '0') || nro_cuil2::CHAR(1)) AS cuil,
                    SUM(CASE WHEN codn_conce IN (306, 308) THEN impp_conce ELSE 0 END)::numeric AS art_contrib
                FROM mapuche.dh21h
                JOIN mapuche.dh01 ON dh21h.nro_legaj = dh01.nro_legaj
                WHERE nro_liqui IN (2, 4, 5)
                GROUP BY dh21h.nro_legaj, nro_cuil1, nro_cuil, nro_cuil2
            )
            SELECT
                a.cuil,
                a.art_contrib,
                ((b.rem_imp9::numeric * 0.005) + 1172) as calculo_teorico,
                (a.art_contrib - ((b.rem_imp9::numeric * 0.005) + 1172)) as diferencia
            FROM dh21art a
            JOIN suc.afip_mapuche_sicoss b ON a.cuil = b.cuil
            WHERE ABS(a.art_contrib - ((b.rem_imp9::numeric * 0.005) + 1172)) > 1
        ");
    }

    /**
     * Obtiene los resultados del control de ART
     *
     * @return array Registros con diferencias en ART
     */
    private function obtenerResultadosControlArt(): array
    {
        return DB::connection($this->getConnection())->select("
            SELECT *
            FROM suc.control_art_diferencias
            ORDER BY diferencia DESC
        ");
    }

    /**
     * Actualiza UA/CAD y Carácter desde Mapuche
     */
    private function actualizarUacadCaracter(): int
    {
        return DB::connection($this->getConnection())->affectingStatement("
            WITH cuils AS (
                SELECT dh01.nro_legaj,
                       CONCAT(dh01.nro_cuil1::text,
                              LPAD(dh01.nro_cuil::CHARACTER(8)::TEXT, 8, '0'::TEXT),
                              dh01.nro_cuil2::text) AS cuil
                FROM mapuche.dh01
                JOIN suc.afip_mapuche_sicoss_calculos am ON
                    CONCAT(dh01.nro_cuil1::text,
                           LPAD(dh01.nro_cuil::CHARACTER(8)::TEXT, 8, '0'::TEXT),
                           dh01.nro_cuil2::text) = am.cuil
            ),
            dh03_agregado AS (
                SELECT nro_legaj,
                       MAX(codc_uacad) as codc_uacad,
                       MIN(codc_carac) as codc_carac
                FROM mapuche.dh03
                GROUP BY nro_legaj
            )
            UPDATE suc.afip_mapuche_sicoss_calculos a
            SET codc_uacad = subq.codc_uacad,
                caracter = CASE
                            WHEN subq.codc_carac IN ('PERM', 'REGU')
                            THEN 'PERM'
                            ELSE 'CONT'
                          END
            FROM (
                SELECT c.cuil, d.codc_uacad, d.codc_carac
                FROM cuils c
                JOIN dh03_agregado d ON c.nro_legaj = d.nro_legaj
            ) subq
            WHERE a.cuil = subq.cuil
        ");
    }
}
