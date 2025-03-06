<?php

declare(strict_types=1);

namespace App\Services\Afip;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Services\Mapuche\TableSelectorService;

class SicossUpdateService
{
    use MapucheConnectionTrait;

    protected TableSelectorService $tableSelectorService;

    public function __construct(TableSelectorService $tableSelectorService)
    {
        $this->tableSelectorService = $tableSelectorService;
    }

    public function executeUpdates(?array $liquidaciones = null): array
    {
        // Si no se proporcionan liquidaciones, usar la liquidación 6 por defecto
        $liquidaciones = $liquidaciones ?: [6];

        $results = [];
        DB::connection($this->getConnectionName())->beginTransaction();

        try {
            // Paso 0: Drop tables si existen
            $results['drop_tables'] = $this->dropTemporaryTables();

            // Paso 1: Crear tabla temporal base
            $results['create_temp_table'] = $this->createTemporaryTables($liquidaciones);

            // Paso 2: Alteraciones de tabla, ya no es necesario
            // $results['alter_tables'] = $this->alterTemporaryTables();

            // Update 1: Actualizar codsit
            $results['update_1_codsit'] = $this->updateCodsit($liquidaciones);

            // Update 2: Actualizar r21
            $results['update_2_r21'] = $this->updateR21();

            // Update 3: Actualizar codact
            $results['update_3_codact'] = $this->updateCodact($liquidaciones);

            // Insert 4: Insertar en mapuche.dha8
            $results['insert_4_dha8'] = $this->insertIntoDha8($liquidaciones);

            // Update 5: Crear tabla temporal Tcodact
            $results['update_5_create_tcodact'] = $this->createTcodact();

            // Update 6: Actualizar valores por defecto
            $results['update_6_defaults'] = $this->updateDefaults();

            // Updates 7-10: Actualizar situaciones específicas
            $results['update_7_situacion'] = $this->updateSituacionCodsit();
            $results['update_8_condicion'] = $this->updateCondicionActividad();
            $results['update_9_condicion_especial'] = $this->updateCondicionEspecial();
            $results['update_10_actividad'] = $this->updateActividad();

            // Verificación final
            $results['verificacion_agentes'] = $this->verificarAgentesInactivos($liquidaciones);

            DB::connection($this->getConnectionName())->commit();
            $results['status'] = 'success';
            $results['message'] = 'Todas las actualizaciones se completaron correctamente';

        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();
            Log::error('Error en actualización SICOSS: ' . $e->getMessage());
            $results['status'] = 'error';
            $results['message'] = 'Error: ' . $e->getMessage();
        }

        return $results;
    }

    public function dropTemporaryTables(): array
    {
        $connection = DB::connection($this->getConnectionName());
        $connection->statement("DROP TABLE IF EXISTS tcargosliq");
        $connection->statement("DROP TABLE IF EXISTS Tcodact");

        return [
            'status' => 'success',
            'message' => 'Tablas temporales eliminadas'
        ];
    }

    public function alterTemporaryTables(): array
    {
        $connection = DB::connection($this->getConnectionName());

        $connection->statement("ALTER TABLE tcargosliq ADD codsit INTEGER DEFAULT 1");
        $connection->statement("ALTER TABLE tcargosliq ADD r21 CHAR(1) DEFAULT 'N'");
        $connection->statement("ALTER TABLE tcargosliq ADD codact INTEGER NULL");

        return [
            'status' => 'success',
            'alterations' => 3
        ];
    }

    public function createTcodact(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->statement("
                SELECT nro_legaj, MIN(codact) AS codact, MAX(codsit) AS codsit
                INTO TEMP Tcodact
                FROM tcargosliq
                GROUP BY nro_legaj
            ");

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function updateSituacionCodsit(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update("
                UPDATE mapuche.dha8
                SET codigosituacion = codsit
                FROM tcodact
                WHERE mapuche.dha8.nro_legajo = tcodact.nro_legaj
                AND codsit = 5
            ");

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function updateCondicionActividad(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update("
                UPDATE mapuche.dha8
                SET codigocondicion = 2,
                    codigoactividad = 17
                FROM tcargosliq
                WHERE tipo_estad = 'J'
                AND NOT ((codc_agrup IN ('AUXU', 'PROU')
                AND tcargosliq.nro_cargo NOT IN (
                    SELECT nro_cargoasociado
                    FROM mapuche.dh90
                    WHERE tipoasociacion = ''
                )) OR r21 = 'S')
                AND mapuche.dha8.nro_legajo = tcargosliq.nro_legaj
            ");

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function updateCondicionEspecial(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update("
                UPDATE mapuche.dha8
                SET codigocondicion = 14
                FROM tcargosliq
                WHERE tipo_estad = 'J'
                AND ((codc_agrup IN ('AUXU', 'PROU')
                AND tcargosliq.nro_cargo NOT IN (
                    SELECT nro_cargoasociado
                    FROM mapuche.dh90
                    WHERE tipoasociacion = ''
                )) OR r21 = 'S')
                AND mapuche.dha8.nro_legajo = tcargosliq.nro_legaj
            ");

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function updateActividad(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update("
                UPDATE mapuche.dha8
                SET codigoactividad = CASE
                    WHEN codact = 1 THEN 37
                    WHEN codact = 2 THEN 35
                    WHEN codact = 3 THEN 88
                    WHEN codact = 4 THEN 17
                END
                FROM Tcodact
                WHERE codigoactividad ISNULL
                AND mapuche.dha8.nro_legajo = Tcodact.nro_legaj
            ");

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function createTemporaryTables(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        $results = ['tables' => [], 'total_affected' => 0];

        try {
            // Se genera una cadena de placeholders según la cantidad de liquidaciones seleccionadas.
            $placeholders = implode(',', array_fill(0, count($liquidaciones), '?'));

            // Determinar qué tabla usar según el período fiscal
            $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

            // 1. Crear tabla temporal base
            $query = "
                SELECT C.codigoescalafon,
                       A.codc_agrup,
                       A.codc_categ,
                       A.codc_carac,
                       C.desc_categ,
                       b.nro_cargo,
                       b.nro_legaj,
                       d.tipo_estad,
                       nro_liqui
                INTO temp tcargosliq
                FROM mapuche.dh03 A,
                     mapuche.{$dh21Table} b,
                     mapuche.dh11 C,
                     mapuche.dh01 d
                WHERE A.nro_legaj = b.nro_legaj
                  AND A.nro_cargo = b.nro_cargo
                  AND A.codc_categ = C.codc_categ
                  AND A.nro_legaj = d.nro_legaj
                  AND nro_liqui IN ($placeholders)
                GROUP BY C.codigoescalafon,
                         A.codc_agrup,
                         A.codc_categ,
                         A.codc_carac,
                         C.desc_categ,
                         b.nro_cargo,
                         b.nro_legaj,
                         d.tipo_estad,
                         nro_liqui
                ORDER BY C.codigoescalafon,
                         A.codc_agrup,
                         A.codc_categ,
                         A.codc_carac,
                         C.desc_categ,
                         b.nro_cargo,
                         b.nro_legaj
            ";

            $affected = $connection->select($query, $liquidaciones);

            $results['tables']['base'] = count($affected);

            // 2. Agregar columnas adicionales
            $connection->statement("ALTER TABLE tcargosliq ADD codsit INTEGER DEFAULT 1");
            $connection->statement("ALTER TABLE tcargosliq ADD r21 CHAR(1) DEFAULT 'N'");
            $connection->statement("ALTER TABLE tcargosliq ADD codact INTEGER NULL");

            $results['tables']['alterations'] = 3;
            $results['total_affected'] = $results['tables']['base'];
            $results['status'] = 'success';

        } catch (\Exception $e) {
            Log::error('Error creando tablas temporales: ' . $e->getMessage());
            throw $e;
        }

        return $results;
    }

    public function updateCodsit(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        // Se genera una cadena de placeholders según la cantidad de liquidaciones seleccionadas.
        $placeholders = implode(',', array_fill(0, count($liquidaciones), '?'));

        // Determinar qué tabla usar según el período fiscal
        $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

        $query = "UPDATE tcargosliq SET codsit = 5
                FROM mapuche.{$dh21Table}
                WHERE mapuche.{$dh21Table}.nro_liqui IN ($placeholders)
                AND tcargosliq.nro_cargo = mapuche.{$dh21Table}.nro_cargo
                AND mapuche.{$dh21Table}.codn_conce = '126'";

        $affected = $connection->update($query, $liquidaciones);

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function updateR21(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update("UPDATE tcargosliq SET r21 = 'S'
                     FROM mapuche.dhd2
                     WHERE tcargosliq.nro_cargo = mapuche.dhd2.nrocargo
                     AND mapuche.dhd2.id_tipoadic = 18
                     AND fechahasta ISNULL");

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function updateCodact(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        // Se genera una cadena de placeholders según la cantidad de liquidaciones seleccionadas.
        $placeholders = implode(',', array_fill(0, count($liquidaciones), '?'));

        // Determinar qué tabla usar según el período fiscal
        $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

        $query = "UPDATE tcargosliq
                     SET codact = CASE
                         WHEN mapuche.{$dh21Table}.codn_conce = 717 THEN 4
                         WHEN mapuche.{$dh21Table}.codn_conce = 735 THEN 2
                         WHEN mapuche.{$dh21Table}.codn_conce = 737 THEN 1
                         WHEN mapuche.{$dh21Table}.codn_conce = 788 THEN 3
                     END
                     FROM mapuche.{$dh21Table}
                     WHERE mapuche.{$dh21Table}.nro_liqui IN ($placeholders)
                     AND tcargosliq.nro_cargo = mapuche.{$dh21Table}.nro_cargo
                     AND tcargosliq.nro_liqui = mapuche.{$dh21Table}.nro_liqui
                     AND mapuche.{$dh21Table}.codn_conce IN (717, 735, 737, 788)";

        $affected = $connection->update($query, $liquidaciones);

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function updateDefaults(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update("UPDATE mapuche.dha8
                     SET codigosituacion = 1,
                         codigocondicion = 1,
                         codigoactividad = NULL,
                         codigozona = 1,
                         codigomodalcontrat = 8");

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function insertIntoDha8(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        // Ejemplo de generación de placeholders si es necesario parametrizar liquidaciones:
        $placeholders = implode(',', array_fill(0, count($liquidaciones), '?'));

        // Determinar qué tabla usar según el período fiscal
        $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

        $query = "
            INSERT INTO mapuche.dha8(nro_legajo,
                                     codigosituacion,
                                     codigocondicion,
                                     codigozona,
                                     codigomodalcontrat,
                                     provincialocalidad)
            SELECT DISTINCT nro_legaj, 1, 1, '1', 8, NULL
            FROM mapuche.{$dh21Table}
            WHERE nro_liqui IN ($placeholders)
              AND nro_legaj NOT IN (SELECT nro_legajo FROM mapuche.dha8)
        ";

        $affected = $connection->update($query, $liquidaciones);

        return [
            'status' => 'success',
            'rows_affected' => $affected
        ];
    }

    public function verificarAgentesInactivos(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        $placeholders = implode(',', array_fill(0, count($liquidaciones), '?'));

        // Determinar qué tabla usar según el período fiscal
        $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

        // Crear tabla temporal aaa
        $connection->statement("DROP TABLE IF EXISTS aaa");
        $query = "
            SELECT DISTINCT nro_legaj
            INTO TEMP aaa
            FROM mapuche.{$dh21Table}
            WHERE nro_liqui IN ($placeholders)
            AND codn_conce = -51
            AND impp_conce > 0
        ";

        $connection->statement($query, $liquidaciones);

        // Obtener agentes sin código de actividad
        $agentes = $connection->select("
            SELECT A.*
            FROM mapuche.dha8 A
            JOIN aaa b ON A.nro_legajo = b.nro_legaj
            WHERE codigoactividad ISNULL
        ");

        return [
            'status' => 'success',
            'agentes_sin_actividad' => $agentes,
            'total_agentes' => count($agentes)
        ];
    }
}
