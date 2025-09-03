<?php

declare(strict_types=1);

namespace App\Services\Afip;

use App\Enums\SicossCodigoActividad;
use App\Enums\SicossCodigoCondicion;
use App\Enums\SicossCodigoModalContrat;
use App\Enums\SicossCodigoSituacion;
use App\Services\Mapuche\TableSelectorService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SicossUpdateService
{
    use MapucheConnectionTrait;

    private const string CODIGO_DOCENTE = 'DOCE';
    private const string CODIGO_EXCLUSIVA = 'EXCL';

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
            // $results['update_6_defaults'] = $this->updateDefaults();

            // Update 6a: Actualizar datos básicos SICOSS desde tcargosliq
            $results['update_6a_basic_sicoss'] = $this->updateBasicSicossData();

            // Updates 7-10: Actualizar situaciones específicas
            $results['update_7_situacion'] = $this->updateSituacionCodsit();
            $results['update_8_condicion'] = $this->updateCondicionActividad();
            $results['update_9_condicion_especial'] = $this->updateCondicionEspecial();
            $results['update_10_actividad'] = $this->updateActividad();
            // Update 11: Actualizar docentes con cargo exclusiva y horas cátedra
            $results['update_11_docentes_exclusiva'] = $this->updateDocentesExclusivaHorasCatedra($liquidaciones);

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

    /**
     * Elimina las tablas temporales utilizadas en el proceso de actualización SICOSS.
     *
     * Este método elimina las tablas temporales 'tcargosliq' y 'Tcodact' si existen.
     * Estas tablas son utilizadas durante el proceso de actualización para almacenar
     * datos intermedios de los cargos y códigos de actividad.
     *
     * @return array Retorna un array con el estado de la operación y un mensaje descriptivo
     *               ['status' => string, 'message' => string]
     */
    public function dropTemporaryTables(): array
    {
        $connection = DB::connection($this->getConnectionName());
        $connection->statement('DROP TABLE IF EXISTS tcargosliq');
        $connection->statement('DROP TABLE IF EXISTS Tcodact');

        return [
            'status' => 'success',
            'message' => 'Tablas temporales eliminadas',
        ];
    }

    /**
     * Altera la tabla temporal tcargosliq agregando columnas necesarias para el proceso SICOSS.
     *
     * Este método agrega las siguientes columnas a la tabla temporal tcargosliq:
     * - codsit: Código de situación del agente (INTEGER, default 1)
     * - r21: Indicador de régimen 21 (CHAR(1), default 'N')
     * - codact: Código de actividad (INTEGER, nullable)
     *
     * @return array Retorna un array con el estado de la operación y cantidad de alteraciones
     *               ['status' => string, 'alterations' => int]
     */
    public function alterTemporaryTables(): array
    {
        $connection = DB::connection($this->getConnectionName());

        $connection->statement('ALTER TABLE tcargosliq ADD codsit INTEGER DEFAULT 1');
        $connection->statement("ALTER TABLE tcargosliq ADD r21 CHAR(1) DEFAULT 'N'");
        $connection->statement('ALTER TABLE tcargosliq ADD codact INTEGER NULL');

        return [
            'status' => 'success',
            'alterations' => 3,
        ];
    }

    /**
     * Crea una tabla temporal Tcodact con información agrupada de tcargosliq.
     *
     * Este método crea una tabla temporal que contiene:
     * - nro_legaj: Número de legajo del agente
     * - codact: Código de actividad mínimo por legajo
     * - codsit: Código de situación máximo por legajo
     *
     * La información se agrupa por número de legajo para consolidar los datos
     * de múltiples registros en tcargosliq.
     *
     * @return array Retorna un array con el estado de la operación y cantidad de filas afectadas
     *               ['status' => string, 'rows_affected' => int]
     */
    public function createTcodact(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->statement('
                SELECT nro_legaj, MIN(codact) AS codact, MAX(codsit) AS codsit
                INTO TEMP Tcodact
                FROM tcargosliq
                GROUP BY nro_legaj
            ');

        return [
            'status' => 'success',
            'rows_affected' => $affected,
        ];
    }

    /**
     * Actualiza el código de situación en la tabla mapuche.dha8 basado en tcodact.
     *
     * Este método actualiza el campo codigosituacion en mapuche.dha8 con el valor
     * de codsit de la tabla temporal tcodact, pero solo cuando codsit es igual a 5.
     * La actualización se realiza haciendo match por número de legajo entre ambas tablas.
     *
     * @return array Retorna un array con el estado de la operación y cantidad de filas afectadas
     *               ['status' => string, 'rows_affected' => int]
     */
    public function updateSituacionCodsit(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update('
                UPDATE mapuche.dha8
                SET codigosituacion = codsit
                FROM tcodact
                WHERE mapuche.dha8.nro_legajo = tcodact.nro_legaj
                AND codsit = ' . SicossCodigoSituacion::MATERNIDAD->value . '
            ');

        return [
            'status' => 'success',
            'rows_affected' => $affected,
        ];
    }

    /**
     * Actualiza la condición de actividad en mapuche.dha8 para registros específicos.
     *
     * Este método actualiza los campos codigocondicion y codigoactividad en mapuche.dha8
     * para registros que cumplen con criterios específicos relacionados con tcargosliq.
     * Se establece codigocondicion = 2 y codigoactividad = 17 para registros donde:
     * - tipo_estad es 'J'
     * - No cumple con las condiciones especiales de AUXU/PROU o r21='S'
     * - Existe match por número de legajo entre las tablas
     *
     * @return array Retorna un array con el estado de la operación y cantidad de filas afectadas
     *               ['status' => string, 'rows_affected' => int]
     */
    public function updateCondicionActividad(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update('
                UPDATE mapuche.dha8
                SET codigocondicion =  ' . SicossCodigoCondicion::JUBILADO->value . ',
                    codigoactividad = ' . SicossCodigoActividad::DOCENTE_ADMINISTRATIVO->value . "
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
            'rows_affected' => $affected,
        ];
    }

    /**
     * Actualiza la condición especial en mapuche.dha8 para registros específicos.
     *
     * Este método actualiza el campo codigocondicion en mapuche.dha8 a 14 para registros
     * que cumplen con criterios específicos relacionados con tcargosliq.
     * Se aplica cuando:
     * - tipo_estad es 'J'
     * - Cumple con las condiciones especiales de AUXU/PROU o r21='S'
     * - Existe match por número de legajo entre las tablas
     *
     * @return array Retorna un array con el estado de la operación y cantidad de filas afectadas
     *               ['status' => string, 'rows_affected' => int]
     */
    public function updateCondicionEspecial(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update('
                UPDATE mapuche.dha8
                SET codigocondicion =  ' . SicossCodigoCondicion::JUBILADO_DOCENTES_UNIVERSITARIOS->value . "
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
            'rows_affected' => $affected,
        ];
    }

    /**
     * Actualiza el código de actividad en mapuche.dha8 basado en los valores de Tcodact.
     *
     * Este método actualiza el campo codigoactividad en mapuche.dha8 para registros
     * que no tienen un código de actividad asignado (ISNULL), mapeando los valores
     * de codact de la tabla Tcodact según la siguiente correspondencia:
     * - codact = 1 -> codigoactividad = DOCENTE_INVESTIGADOR (37)
     * - codact = 2 -> codigoactividad = DOCENTE_UNIVERSITARIO (35)
     * - codact = 3 -> codigoactividad = DOCENTE_ESPECIAL (88)
     * - codact = 4 -> codigoactividad = DOCENTE_ADMINISTRATIVO (17)
     *
     * La actualización se realiza solo para registros que coinciden por número de legajo
     * entre las tablas mapuche.dha8 y Tcodact.
     *
     * @return array Retorna un array con el estado de la operación y cantidad de filas afectadas
     *               ['status' => string, 'rows_affected' => int]
     */
    public function updateActividad(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update('
                UPDATE mapuche.dha8
                SET codigoactividad = CASE
                    WHEN codact = 1 THEN ' . SicossCodigoActividad::fromCodact(1)->value . '
                    WHEN codact = 2 THEN ' . SicossCodigoActividad::fromCodact(2)->value . '
                    WHEN codact = 3 THEN ' . SicossCodigoActividad::fromCodact(3)->value . '
                    WHEN codact = 4 THEN ' . SicossCodigoActividad::fromCodact(4)->value . '
                END
                FROM Tcodact
                WHERE codigoactividad ISNULL
                AND mapuche.dha8.nro_legajo = Tcodact.nro_legaj
            ');

        return [
            'status' => 'success',
            'rows_affected' => $affected,
        ];
    }

    /**
     * Actualiza la actividad de docentes con dedicación exclusiva y horas cátedra.
     *
     * Este método identifica y actualiza los registros de docentes que tienen dedicación exclusiva
     * en las unidades académicas AGX (324) y CXX (030), cambiando su código de actividad de 1 a 2.
     *
     * El proceso se realiza en dos pasos:
     * 1. Identifica los legajos relevantes usando CTEs (Common Table Expressions)
     * 2. Actualiza los registros que cumplen con los criterios establecidos
     *
     * @param array $liquidaciones Números de liquidación a procesar (por defecto [6])
     *
     * @return array Resultado de la operación con estado y cantidad de filas afectadas
     */
    public function updateDocentesExclusivaHorasCatedra(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        $placeholders = implode(',', array_fill(0, \count($liquidaciones), '?'));
        $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

        $query = "
                WITH legajos_agx AS (
                    SELECT DISTINCT d.nro_legaj
                    FROM mapuche.dh03 d
                    JOIN mapuche.{$dh21Table} d1 ON d.nro_cargo = d1.nro_cargo
                    WHERE coddependesemp LIKE '%324%'
                    AND d1.nro_liqui IN ($placeholders)
                    AND d.hs_dedic > 0
                    AND d.codc_uacad = 'AGX'
                ), legajos_cxx AS (
                    SELECT DISTINCT d.nro_legaj, d.coddependesemp, d.hs_dedic, d2.codc_dedic, d.nro_cargo
                    FROM mapuche.dh03 d
                    JOIN mapuche.{$dh21Table} d1 ON d.nro_cargo = d1.nro_cargo
                    JOIN mapuche.dh11 d2 ON d.codc_categ = d2.codc_categ
                    WHERE coddependesemp LIKE '%030%'
                    AND d1.nro_liqui IN ($placeholders)
                    AND d.hs_dedic > 0
                    AND d.codc_uacad = 'CXX'
                    ORDER BY 1
                ), resultado_agx AS (
                    SELECT DISTINCT d.nro_legaj, d.nro_cargo, d2.codc_categ, d2.codc_dedic, d2.codigoescalafon, d1.codc_uacad, d.coddependesemp
                    FROM mapuche.dh03 d
                    JOIN mapuche.{$dh21Table} d1 ON d.nro_cargo = d1.nro_cargo
                    JOIN mapuche.dh11 d2 ON d.codc_categ = d2.codc_categ
                    JOIN legajos_agx a ON d.nro_legaj = a.nro_legaj
                    WHERE d1.nro_liqui IN ($placeholders)
                    AND d2.codigoescalafon = 'DOCE'
                    AND d2.codc_dedic = 'EXCL'
                    ORDER BY 2
                ), resultados_cxx AS (
                    SELECT DISTINCT d.nro_legaj, d.nro_cargo, d2.codc_categ, d2.codc_dedic, d2.codigoescalafon, d1.codc_uacad, d.coddependesemp
                    FROM mapuche.dh03 d
                    JOIN mapuche.{$dh21Table} d1 ON d.nro_cargo = d1.nro_cargo
                    JOIN mapuche.dh11 d2 ON d.codc_categ = d2.codc_categ
                    JOIN legajos_cxx b ON d.nro_legaj = b.nro_legaj
                    WHERE d1.nro_liqui IN ($placeholders)
                    AND d2.codigoescalafon = 'DOCE'
                    AND d2.codc_dedic = 'EXCL'
                    ORDER BY 2
                ), resultado AS (
                    SELECT *
                    FROM resultado_agx
                    UNION ALL
                    SELECT *
                    FROM resultados_cxx
                    ORDER BY nro_legaj, codc_categ
                )
                UPDATE tcargosliq SET codact = 2
                FROM resultado
                JOIN tcargosliq t ON resultado.nro_legaj = t.nro_legaj AND t.codact = 1
            ";

        // Pasar los parámetros 4 veces, una por cada CTE que los usa
        $params = array_merge(
            $liquidaciones,  // Para legajos_agx
            $liquidaciones,  // Para legajos_cxx
            $liquidaciones,  // Para resultado_agx
            $liquidaciones,   // Para resultados_cxx
        );
        $affected = $connection->update($query, $params);

        return [
            'status' => 'success',
            'rows_affected' => $affected,
        ];
    }

    /**
     * Crea tablas temporales para el procesamiento de liquidaciones.
     *
     * Este método realiza las siguientes operaciones:
     * 1. Crea una tabla temporal 'tcargosliq' con datos de las tablas mapuche
     * 2. Agrega columnas adicionales necesarias para el procesamiento
     *
     * @param array $liquidaciones Array de IDs de liquidaciones a procesar. Por defecto [6]
     *
     * @throws \Exception Si ocurre un error durante la creación de las tablas
     *
     * @return array{
     *     tables: array{
     *         base: int,
     *         alterations: int
     *     },
     *     total_affected: int,
     *     status: string
     * }
     */
    public function createTemporaryTables(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        $results = ['tables' => [], 'total_affected' => 0];

        try {
            // Se genera una cadena de placeholders según la cantidad de liquidaciones seleccionadas.
            $placeholders = implode(',', array_fill(0, \count($liquidaciones), '?'));

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

            $results['tables']['base'] = \count($affected);

            // 2. Agregar columnas adicionales
            $connection->statement('ALTER TABLE tcargosliq ADD codsit INTEGER DEFAULT 1');
            $connection->statement("ALTER TABLE tcargosliq ADD r21 CHAR(1) DEFAULT 'N'");
            $connection->statement('ALTER TABLE tcargosliq ADD codact INTEGER NULL');

            $results['tables']['alterations'] = 3;
            $results['total_affected'] = $results['tables']['base'];
            $results['status'] = 'success';
        } catch (\Exception $e) {
            Log::error('Error creando tablas temporales: ' . $e->getMessage());
            throw $e;
        }

        return $results;
    }

    /**
     * Actualiza el campo codsit en la tabla temporal tcargosliq para las liquidaciones especificadas.
     *
     * Este método actualiza el campo codsit a 5 para los registros que coincidan con el concepto 126
     * en la tabla DH21 correspondiente al período fiscal de las liquidaciones.
     *
     * @param array $liquidaciones Array de números de liquidación a procesar. Por defecto [6].
     *
     * @throws \Exception Si ocurre un error durante la actualización.
     *
     * @return array{status: string, rows_affected: int} Array con el estado de la operación y el número de filas afectadas.
     */
    public function updateCodsit(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        // Se genera una cadena de placeholders según la cantidad de liquidaciones seleccionadas.
        $placeholders = implode(',', array_fill(0, \count($liquidaciones), '?'));

        // Determinar qué tabla usar según el período fiscal
        $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

        $query = 'UPDATE tcargosliq SET codsit = ' . SicossCodigoSituacion::MATERNIDAD->value . "
                FROM mapuche.{$dh21Table}
                WHERE mapuche.{$dh21Table}.nro_liqui IN ($placeholders)
                AND tcargosliq.nro_cargo = mapuche.{$dh21Table}.nro_cargo
                AND mapuche.{$dh21Table}.codn_conce = '126'";

        $affected = $connection->update($query, $liquidaciones);

        return [
            'status' => 'success',
            'rows_affected' => $affected,
        ];
    }

    /**
     * Actualiza el campo r21 en la tabla temporal tcargosliq para los registros que coincidan con el tipo adicional 18.
     *
     * Este método actualiza el campo r21 a 'S' para los registros que tienen un tipo adicional 18
     * en la tabla dhd2 y no tienen fecha de finalización (fechahasta ISNULL).
     *
     * @throws \Exception Si ocurre un error durante la actualización.
     *
     * @return array{status: string, rows_affected: int} Array con el estado de la operación y el número de filas afectadas.
     */
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
            'rows_affected' => $affected,
        ];
    }

    /**
     * Actualiza el campo codact en la tabla tcargosliq basado en los códigos de concepto de la tabla DH21.
     *
     * Este método actualiza el campo codact según la siguiente lógica:
     * - Si codn_conce = 717, establece codact = 4
     * - Si codn_conce = 735, establece codact = 2
     * - Si codn_conce = 737, establece codact = 1
     * - Si codn_conce = 788, establece codact = 3
     *
     * @param array<int> $liquidaciones Array de números de liquidación a procesar. Por defecto [6]
     *
     * @throws \Exception Si ocurre un error durante la actualización
     *
     * @return array{
     *     status: string,
     *     rows_affected: int
     * } Array con el estado de la operación y el número de filas afectadas
     */
    public function updateCodact(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        // Se genera una cadena de placeholders según la cantidad de liquidaciones seleccionadas.
        $placeholders = implode(',', array_fill(0, \count($liquidaciones), '?'));

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
            'rows_affected' => $affected,
        ];
    }

    /**
     * Actualiza los datos básicos de SICOSS con valores estándar desde tcargosliq.
     *
     * Este método actualiza la tabla mapuche.dha8 con los valores estándar establecidos
     * desde la tabla temporal suc.tcargosliq para garantizar la consistencia de los datos
     * SICOSS básicos antes de aplicar actualizaciones específicas.
     * Este método establece los siguientes valores por defecto para todos los registros:
     * 
     * - codigosituacion = ACTIVO (1)
     * - codigocondicion = SERVICIOS_COMUNES_MAYOR_18 (1)
     * - codigoactividad = NULL
     * - codigozona = 1
     * - codigomodalcontrat = TIEMPO_COMPLETO_INDETERMINADO (8)
     * 
     * @return array{
     *     status: string,
     *     rows_affected: int
     * } Array con el estado de la operación y el número de filas afectadas
     */
    public function updateBasicSicossData(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update('UPDATE mapuche.dha8 
                    SET codigosituacion = ' . SicossCodigoSituacion::ACTIVO->value . ', 
                        codigocondicion = ' . SicossCodigoCondicion::SERVICIOS_COMUNES_MAYOR_18->value . ', 
                        codigoactividad = null, 
                        codigozona = 1, 
                        codigomodalcontrat = ' . SicossCodigoModalContrat::TIEMPO_COMPLETO_INDETERMINADO->value . '
                    FROM suc.tcargosliq 
                    WHERE suc.tcargosliq.nro_legaj = mapuche.dha8.nro_legajo');

        return [
            'status' => 'success',
            'rows_affected' => $affected,
        ];
    }

    /**
     * Actualiza los valores por defecto en la tabla mapuche.dha8.
     *
     * Este método establece los siguientes valores por defecto para todos los registros:
     * - codigosituacion = ACTIVO (1)
     * - codigocondicion = SERVICIOS_COMUNES_MAYOR_18 (1)
     * - codigoactividad = NULL
     * - codigozona = 1
     * - codigomodalcontrat = TIEMPO_COMPLETO_INDETERMINADO (8)
     *
     * @return array{
     *     status: string,
     *     rows_affected: int
     * } Array con el estado de la operación y el número de filas afectadas
     */
    public function updateDefaults(): array
    {
        $affected = DB::connection($this->getConnectionName())
            ->update('UPDATE mapuche.dha8
                    SET codigosituacion = ' . SicossCodigoSituacion::ACTIVO->value . ',
                        codigocondicion =  ' . SicossCodigoCondicion::SERVICIOS_COMUNES_MAYOR_18->value . ',
                        codigoactividad = NULL,
                        codigozona = 1,
                        codigomodalcontrat = ' . SicossCodigoModalContrat::TIEMPO_COMPLETO_INDETERMINADO->value);

        return [
            'status' => 'success',
            'rows_affected' => $affected,
        ];
    }

    /**
     * Inserta registros en la tabla dha8 para legajos que no existen.
     *
     * Este método realiza las siguientes operaciones:
     * 1. Inserta registros en mapuche.dha8 para legajos que existen en la tabla DH21
     *    pero no en dha8
     * 2. Establece valores por defecto para los campos:
     *    - codigosituacion = ACTIVO (1)
     *    - codigocondicion = SERVICIOS_COMUNES_MAYOR_18 (1)
     *    - codigozona = '1'
     *    - codigomodalcontrat = TIEMPO_COMPLETO_INDETERMINADO (8)
     *    - provincialocalidad = NULL
     *
     * @param array<int> $liquidaciones Array de números de liquidación a procesar. Por defecto [6]
     *
     * @return array{
     *     status: string,
     *     rows_affected: int
     * }
     */
    public function insertIntoDha8(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        // Ejemplo de generación de placeholders si es necesario parametrizar liquidaciones:
        $placeholders = implode(',', array_fill(0, \count($liquidaciones), '?'));

        // Determinar qué tabla usar según el período fiscal
        $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

        $query = '
            INSERT INTO mapuche.dha8(nro_legajo,
                                    codigosituacion,
                                    codigocondicion,
                                    codigozona,
                                    codigomodalcontrat,
                                    provincialocalidad)
            SELECT DISTINCT nro_legaj,
                ' . SicossCodigoSituacion::ACTIVO->value . ',
                ' . SicossCodigoCondicion::SERVICIOS_COMUNES_MAYOR_18->value . ",
                '1',
                " . SicossCodigoModalContrat::TIEMPO_COMPLETO_INDETERMINADO->value . ",
                NULL
            FROM mapuche.{$dh21Table}
            WHERE nro_liqui IN ($placeholders)
                AND nro_legaj NOT IN (SELECT nro_legajo FROM mapuche.dha8)
        ";

        $affected = $connection->update($query, $liquidaciones);

        return [
            'status' => 'success',
            'rows_affected' => $affected,
        ];
    }

    /**
     * Verifica los agentes inactivos en el sistema SICOSS.
     *
     * Este método realiza las siguientes operaciones:
     * 1. Crea una tabla temporal con los legajos de agentes que tienen concepto -51 (inactividad)
     * 2. Identifica los agentes que no tienen código de actividad asignado
     *
     * @param array<int> $liquidaciones Array de números de liquidación a verificar. Por defecto [6]
     *
     * @return array{
     *     status: string,
     *     agentes_sin_actividad: array,
     *     total_agentes: int
     * }
     */
    public function verificarAgentesInactivos(array $liquidaciones = [6]): array
    {
        $connection = DB::connection($this->getConnectionName());
        $placeholders = implode(',', array_fill(0, \count($liquidaciones), '?'));

        // Determinar qué tabla usar según el período fiscal
        $dh21Table = $this->tableSelectorService->getDh21TableName($liquidaciones);

        // Crear tabla temporal aaa
        $connection->statement('DROP TABLE IF EXISTS aaa');
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
        $agentes = $connection->select('
            SELECT A.*
            FROM mapuche.dha8 A
            JOIN aaa b ON A.nro_legajo = b.nro_legaj
            WHERE codigoactividad ISNULL
        ');

        return [
            'status' => 'success',
            'agentes_sin_actividad' => $agentes,
            'total_agentes' => \count($agentes),
        ];
    }
}
