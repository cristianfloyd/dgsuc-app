<?php

namespace App\Services;

use App\QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Reportes\RepGerFinal;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Services\Mapuche\PeriodoFiscalService;

class RepGerencialFinalService
{
    use MapucheConnectionTrait;

    protected string $connection;
    protected string $schema = 'suc';
    protected string $table = 'rep_ger_final';

    public function __construct(private readonly PeriodoFiscalService $periodoFiscalService)
    {
        $this->connection = $this->getConnectionName();
    }

    public function createTable(): void
    {
        $fullTableName = "{$this->schema}.{$this->table}";

        if (Schema::connection($this->connection)->hasTable($fullTableName)) {
            Schema::connection($this->connection)->drop($fullTableName);
        }

        Schema::connection($this->connection)->create($fullTableName, function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('codn_fuent');
            $table->integer('codn_depen');
            $table->text('tipo_ejercicio');
            $table->text('codn_grupo_presup');
            $table->text('codn_area');
            $table->text('codn_subar');
            $table->text('codn_subsubar');
            $table->text('codn_progr');
            $table->text('codn_subpr');
            $table->text('codn_proye');
            $table->text('codn_activ');
            $table->text('codn_obra');
            $table->text('codn_final');
            $table->text('codn_funci');
            $table->text('codn_imput');
            $table->text('imputacion');
            $table->integer('nro_inciso');
            $table->integer('nro_legaj');
            $table->text('desc_apyno');
            $table->string('nombre_elegido', 250)->nullable();
            $table->string('apellido_elegido', 250)->nullable();
            $table->integer('cant_anios');
            $table->double('ano_antig');
            $table->double('mes_antig');
            $table->integer('nro_cargo');
            $table->char('codc_categ', 4);
            $table->char('codc_dedic', 4);
            $table->char('tipo_escal', 1);
            $table->char('codc_carac', 4);
            $table->char('codc_uacad', 4);
            $table->char('codc_regio', 4);
            $table->date('fecha_alta')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->decimal('porc_imput', 10, 2);
            $table->decimal('imp_gasto', 10, 2);
            $table->decimal('imp_bruto', 10, 2);
            $table->decimal('imp_neto', 10, 2);
            $table->decimal('imp_dctos', 10, 2);
            $table->decimal('imp_aport', 10, 2);
            $table->decimal('imp_familiar', 10, 2);
            $table->integer('ano_liqui');
            $table->integer('mes_liqui');
            $table->integer('nro_liqui');
            $table->char('tipo_estad', 1);
            $table->text('cuil');
            $table->double('hs_catedra');
            $table->double('dias_trab');
            $table->decimal('rem_c_apor', 10, 2);
            $table->decimal('otr_no_rem', 10, 2);
            $table->text('en_banco');
            $table->char('coddependesemp', 4);
            $table->double('porc_aplic');
            $table->integer('cod_clasif_cargo');
            $table->char('tipo_carac', 1);
            $table->decimal('rem_s_apor', 10, 2);

            $table->index(['nro_liqui', 'coddependesemp']);
            $table->index('nro_legaj');
        });
    }

    public function truncateFinalTable(): void
    {
        $fullTableName = "{$this->schema}.{$this->table}";
        DB::connection($this->connection)->statement("TRUNCATE TABLE {$fullTableName}");
        Log::info('Tabla rep_ger_final truncada exitosamente');
    }


    public function processReport(?array $liquidaciones, array $filters = []): void
    {
        Log::info('Procesando datos de rep_ger_final', ['liquidaciones' => $liquidaciones]);

        // Chequear que $liquidaciones no esté vacío
        if (empty($liquidaciones)) {
            Log::warning('No se encontraron liquidaciones para procesar.');
            return;
        }

        try {
            DB::connection($this->connection)->beginTransaction();




            // Limpiamos la tabla final al inicio de un nuevo reporte
            $this->truncateFinalTable();


            // Procesamos cada liquidacion individualmente
            foreach ($liquidaciones as $liquidacion) {
                $liquidacionActual = $liquidacion;
                $whereClause = $this->buildFilters($filters);

                $this->dropPreviousTables();

                // Procesamos cada parte del reporte
                $this->processBasicData($liquidacionActual, $whereClause, $filters);
                $this->processNetAmountsTypeC($liquidacionActual, $whereClause, $filters);
                $this->processNetAmountsTypeS($liquidacionActual, $whereClause, $filters);
                $this->processNetAmountsTypeO($liquidacionActual, $whereClause, $filters);
                $this->processNetAmountsTypeF($liquidacionActual, $whereClause, $filters);
                $this->processNetAmountsTypeD($liquidacionActual, $whereClause, $filters);
                $this->processNetAmountsTypeA($liquidacionActual, $whereClause, $filters);
                $this->consolidateNetAmounts();
                $this->processAntiguedad($liquidacionActual, $whereClause, $filters);
                $this->processHorasTrabajadas($liquidacionActual, $whereClause, $filters);

                $this->appendToFinalReport();

                // Limpiamos tablas previas si existen
                $this->dropPreviousTables();
            }

            DB::connection($this->connection)->commit();

            Log::info('Reporte gerencial generado exitosamente', [
                'liquidaciones' => $liquidaciones
            ]);
        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error('Error al generar reporte gerencial', [
                'error' => $e->getMessage(),
                'liquidaciones' => $liquidaciones,
                'nro_legaj' => $filters['nro_legaj']
            ]);
            throw $e;
        }
    }


    protected function buildFilters(array $filters = []): string
    {
        $conditions = [];

        if (!empty($filters['codc_regio'])) {
            $conditions[] = "dh21.codc_regio = :codc_regio";
        }

        if (!empty($filters['nro_liqui'])) {
            $conditions[] = "dh21.nro_liqui = :nro_liqui";
        }

        if (!empty($filters['codc_uacad'])) {
            $conditions[] = "dh21.codc_uacad = :codc_uacad";
        }

        if (!empty($filters['codigoescalafon'])) {
            $conditions[] = "dh21.codigoescalafon = :codigoescalafon";
        }

        if (!empty($filters['codc_carac'])) {
            $conditions[] = "dh03.codc_carac = :codc_carac";
        }

        if (!empty($filters['nro_legaj'])) {
            $conditions[] = "dh01.nro_legaj = :nro_legaj";
        }

        if (!empty($filters['codn_fuent'])) {
            $conditions[] = "dh21.codn_fuent = :codn_fuent";
        }

        if (!empty($filters['codn_area'])) {
            $conditions[] = "codn_area = :codn_area";
        }

        if (!empty($filters['conceptos'])) {
            $conceptosConditions = array_map(
                fn($concepto) => "dh21.codn_conce = :concepto_{$concepto}",
                $filters['conceptos']
            );
            $conditions[] = '(' . implode(' OR ', $conceptosConditions) . ')';
        }

        // Conceptos mayores que 0
        if (empty($filters['conceptos'])) {
            $conditions[] = "dh21.codn_conce > 0";
            $conditions[] = "dh21.nro_orimp > 0";
        }

        return empty($conditions) ? 'TRUE' : implode(' AND ', $conditions);
    }


    protected function addFilters(string $sql, ?string $whereClause): string
    {
        if ($whereClause) {
            $sql = str_replace(
                'WHERE TRUE',
                "WHERE TRUE AND $whereClause",
                $sql
            );
        }

        return $sql;
    }

    protected function getBindings(array $filters): array
    {
        return array_filter([
            'codc_regio' => $filters['codc_regio'] ?? null,
            'codc_uacad' => $filters['codc_uacad'] ?? null,
            'codigoescalafon' => $filters['codigoescalafon'] ?? null,
            'codc_carac' => $filters['codc_carac'] ?? null,
            'nro_legaj' => $filters['nro_legaj'] ?? null,
            'codn_fuent' => $filters['codn_fuent'] ?? null,
            'codn_area' => $filters['codn_area'] ?? null,
        ]);
    }

    protected function buildQuery(string $baseSql, array $filters): array
    {
        return [
            'query' => $this->addFilters($baseSql, $this->buildFilters($filters)),
            'bindings' => $this->getBindings($filters)
        ];
    }

    protected function executeQuery(string $sql, array $filters): void
    {
        $query = $this->buildQuery($sql, $filters);
        DB::connection($this->connection)->statement($query['query'], $query['bindings']);
    }


    /* #################### FUNCIONES #################### */
    public function dropPreviousTables(): void
    {
        $tables = [
            'rep_ger_datos_base_dh21',
            'rep_ger_importes_netos_c',
            'rep_ger_importes_netos_s',
            'rep_ger_importes_netos_o',
            'rep_ger_importes_netos_f',
            'rep_ger_importes_netos_d',
            'rep_ger_importes_netos_a',
            'rep_ger_importes_netos',
            'rep_ger_datos_antiguedad',
            'rep_ger_horas_trabajadas',
        ];

        foreach ($tables as $table) {
            $fullTableName = "{$this->schema}.{$table}";
            if (Schema::connection($this->connection)->hasTable($fullTableName)) {
                Schema::connection($this->connection)->drop($fullTableName);
                Log::info("Tabla {$fullTableName} eliminada");
            }
        }
    }

    protected function truncatePreviousTables(): void
    {
        $tables = [
            'rep_ger_datos_base_dh21',
            'rep_ger_importes_netos_c',
            'rep_ger_importes_netos_s',
            'rep_ger_importes_netos_o',
            'rep_ger_importes_netos_f',
            'rep_ger_importes_netos_d',
            'rep_ger_importes_netos_a',
            'rep_ger_importes_netos',
            'rep_ger_datos_antiguedad',
            'rep_ger_horas_trabajadas',
        ];

        foreach ($tables as $table) {
            $fullTableName = "{$this->schema}.{$table}";
            if (Schema::connection($this->connection)->hasTable($fullTableName)) {
                DB::connection($this->connection)->statement("TRUNCATE TABLE {$fullTableName}");
                Log::info("Tabla {$fullTableName} vaciada");
            }
        }
    }

    protected function processBasicData(?int $liquidaciones, ?string $whereClause = null, array $filters): void
    {
        $isPeriodoActual = $this->periodoFiscalService->isPeriodoActual($liquidaciones);
        $tabla = $isPeriodoActual ? 'mapuche.dh21' : 'mapuche.dh21h';

        $sql = "
        SELECT DISTINCT
            dh21.codn_fuent,
            dh21.tipo_ejercicio,
            dh21.codn_grupo_presup,
            dh21.codn_area AS codn_depen,
            dh21.codn_subar,
            dh21.codn_subsubar,
            dh21.codn_progr,
            dh21.codn_subpr,
            dh21.codn_proye,
            dh21.codn_activ,
            dh21.codn_obra,
            dh21.codn_final,
            dh21.codn_funci,
            (LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') ||
             LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
             LPAD(dh21.codn_area::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_progr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_subpr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_proye::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_activ::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_final::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
            COALESCE(
                CASE
                    WHEN dh35.tipo_carac = 'T' THEN
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtote, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtote, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtote, 1, 1)::INT
                        END
                    ELSE
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtope, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtope, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtope, 1, 1)::INT
                        END
                END, 1) AS nro_inciso,
            dh21.nro_legaj,
            dh21.nro_cargo,
            dh03.codc_categ,
            dh03.coddependesemp,
            dh03.porc_aplic,
            dh03.cod_clasif_cargo,
            dh35.tipo_carac,
            CASE WHEN cuenta.nrolegajo IS NULL THEN 'N' ELSE 'S' END AS en_banco,
            CASE WHEN dh21.tipoescalafon = 'C' THEN 'S' ELSE dh21.tipoescalafon END AS tipo_escal,
            dh21.codc_regio,
            dh03.codc_carac,
            dh03.fec_alta AS fecha_alta,
            dh03.fec_baja AS fecha_baja,
            COALESCE(dh24.porc_ipres, 0.00) AS porc_imput,
            dh22.per_liano AS ano_liqui,
            dh22.per_limes AS mes_liqui,
            dh22.nro_liqui,
            dh21.codigoescalafon,
            dh21.codc_uacad,
            dh03.codc_agrup,
            dh03.hs_dedic
        INTO TABLE {$this->schema}.rep_ger_datos_base_dh21
        FROM {$tabla} dh21
        JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
        LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
        LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
        LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
        LEFT OUTER JOIN mapuche.dh35 ON (
            (dh35.tipo_escal = dh21.tipoescalafon OR (dh21.tipoescalafon = 'C' AND dh35.tipo_escal = 'S')) AND
            dh35.codc_carac = dh03.codc_carac)
        LEFT OUTER JOIN mapuche.dh24 ON (dh24.nro_cargo = dh21.nro_cargo AND dh24.codn_progr = dh21.codn_progr AND
                                      dh24.codn_subpr = dh21.codn_subpr AND dh24.codn_proye = dh21.codn_proye AND
                                      dh24.codn_activ = dh21.codn_activ AND dh24.codn_obra = dh21.codn_obra AND
                                      dh24.codn_area = dh21.codn_area AND dh24.codn_subar = dh21.codn_subar AND
                                      dh24.codn_subsubar = dh21.codn_subsubar AND
                                      dh24.codn_final = dh21.codn_final AND dh24.codn_funci = dh21.codn_funci AND
                                      dh24.tipo_ejercicio = dh21.tipo_ejercicio AND
                                      dh24.codn_grupo_presup = dh21.codn_grupo_presup AND
                                      dh24.codn_fuent = dh21.codn_fuent)
        LEFT JOIN (SELECT DISTINCT nrolegajo FROM mapuche.dh92) cuenta ON (dh03.nro_legaj = cuenta.nrolegajo)
        WHERE TRUE AND dh21.nro_liqui = ". $liquidaciones . "
        ORDER BY dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh22.nro_liqui, nro_inciso";

        $queryBuilder = new QueryBuilder($sql);
        $finalSql = $this->addFilters($queryBuilder->getSql(), $whereClause);
        $bindings = $this->getBindings($filters);


        DB::connection($this->connection)->statement($finalSql, $bindings);
    }


    protected function processNetAmountsTypeC(?int $liquidaciones, ?string $whereClause = null, array $filters): void
    {
        $sql = "
        SELECT
            dh21.codn_fuent,
            (LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') ||
             LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
             LPAD(dh21.codn_area::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_progr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_subpr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_proye::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_activ::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_final::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
            dh21.nro_legaj,
            dh21.nro_cargo,
            dh21.nro_liqui,
            COALESCE(
                CASE
                    WHEN dh35.tipo_carac = 'T' THEN
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtote, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtote, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtote, 1, 1)::INT
                        END
                    ELSE
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtope, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtope, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtope, 1, 1)::INT
                        END
                END, 1) AS nro_inciso,
            SUM(dh21.impp_conce)::NUMERIC(10, 2) AS netos_c,
            SUM(CASE
                WHEN dh21.codn_conce IN (121, 122, 124, 125)
                THEN impp_conce
                ELSE 0
            END)::NUMERIC(10, 2) AS hs_extras
        INTO TABLE {$this->schema}.rep_ger_importes_netos_c
        FROM mapuche.dh21 dh21
        JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
        LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
        LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
        LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
        LEFT OUTER JOIN mapuche.dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
        WHERE TRUE
        AND dh21.tipo_conce = 'C' AND dh21.nro_orimp > 0
        AND dh21.nro_liqui = ". $liquidaciones ."
        GROUP BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
        ORDER BY dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso";

        $queryBuilder = new QueryBuilder($sql);
        $finalSql = $this->addFilters($queryBuilder->getSql(), $whereClause);
        $bindings = $this->getBindings($filters);

        DB::connection($this->connection)->statement($finalSql, $bindings);
    }

    protected function processNetAmountsTypeS(?int $liquidaciones, ?string $whereClause = null, array $filters): void
    {
        $sql = "
        SELECT
            dh21.codn_fuent,
            (LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') ||
             LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
             LPAD(dh21.codn_area::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_progr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_subpr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_proye::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_activ::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_final::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
            dh21.nro_legaj,
            dh21.nro_cargo,
            dh21.nro_liqui,
            COALESCE(
                CASE
                    WHEN dh35.tipo_carac = 'T' THEN
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtote, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtote, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtote, 1, 1)::INT
                        END
                    ELSE
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtope, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtope, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtope, 1, 1)::INT
                        END
                END, 1) AS nro_inciso,
            SUM(dh21.impp_conce)::NUMERIC(10, 2) AS netos_s
        INTO TABLE {$this->schema}.rep_ger_importes_netos_s
        FROM mapuche.dh21 dh21
        JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
        LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
        LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
        LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
        LEFT OUTER JOIN mapuche.dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
        WHERE TRUE AND dh21.tipo_conce = 'S'  AND dh21.nro_orimp > 0
        AND dh21.nro_liqui = ". $liquidaciones ."
        GROUP BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
        ORDER BY dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso";

        $queryBuilder = new QueryBuilder($sql);
        $finalSql = $this->addFilters($queryBuilder->getSql(), $whereClause);
        $bindings = $this->getBindings($filters);

        DB::connection($this->connection)->statement($finalSql, $bindings);
    }

    protected function processNetAmountsTypeO(?int $liquidaciones, ?string $whereClause = null, array $filters): void
    {
        $sql = "
        SELECT
            dh21.codn_fuent,
            (LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') ||
             LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
             LPAD(dh21.codn_area::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_progr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_subpr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_proye::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_activ::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_final::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
            dh21.nro_legaj,
            dh21.nro_cargo,
            dh21.nro_liqui,
            COALESCE(
                CASE
                    WHEN dh35.tipo_carac = 'T' THEN
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtote, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtote, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtote, 1, 1)::INT
                        END
                    ELSE
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtope, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtope, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtope, 1, 1)::INT
                        END
                END, 1) AS nro_inciso,
            SUM(dh21.impp_conce)::NUMERIC(10, 2) AS netos_o
        INTO TABLE {$this->schema}.rep_ger_importes_netos_o
        FROM mapuche.dh21 dh21
        JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
        LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
        LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
        LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
        LEFT OUTER JOIN mapuche.dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
        WHERE TRUE AND dh21.tipo_conce = 'O' AND dh21.nro_orimp > 0
        AND dh21.nro_liqui = ". $liquidaciones ."
        GROUP BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
        ORDER BY dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso";

        $queryBuilder = new QueryBuilder($sql);
        $finalSql = $this->addFilters($queryBuilder->getSql(), $whereClause);
        $bindings = $this->getBindings($filters);

        DB::connection($this->connection)->statement($finalSql, $bindings);
    }

    protected function processNetAmountsTypeF(?int $liquidaciones, ?string $whereClause = null, array $filters): void
    {
        $sql = "
        SELECT
            dh21.codn_fuent,
            (LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') ||
             LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
             LPAD(dh21.codn_area::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_progr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_subpr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_proye::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_activ::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_final::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
            dh21.nro_legaj,
            dh21.nro_cargo,
            dh21.nro_liqui,
            COALESCE(
                CASE
                    WHEN dh35.tipo_carac = 'T' THEN
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtote, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtote, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtote, 1, 1)::INT
                        END
                    ELSE
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtope, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtope, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtope, 1, 1)::INT
                        END
                END, 1) AS nro_inciso,
            SUM(dh21.impp_conce)::NUMERIC(10, 2) AS netos_f
        INTO TABLE {$this->schema}.rep_ger_importes_netos_f
        FROM mapuche.dh21 dh21
        JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
        LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
        LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
        LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
        LEFT OUTER JOIN mapuche.dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
        WHERE TRUE
        AND dh21.tipo_conce = 'F'  AND dh21.nro_orimp > 0
        AND dh21.nro_liqui = ". $liquidaciones ."
        GROUP BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
        ORDER BY dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso";

        $queryBuilder = new QueryBuilder($sql);
        $finalSql = $this->addFilters($queryBuilder->getSql(), $whereClause);
        $bindings = $this->getBindings($filters);

        DB::connection($this->connection)->statement($finalSql, $bindings);
    }

    /**
     * Procesa los importes netos de tipo 'D' para el informe gerencial final.
     * Este método ejecuta una consulta SQL que obtiene información de varias tablas
     * de la base de datos 'mapuche' y almacena los resultados en la tabla
     * 'rep_ger_importes_netos_d' del esquema definido en la propiedad '$this->schema'.
     *
     * @param array $liquidaciones Array de números de liquidación a procesar
     * @return void
     */
    protected function processNetAmountsTypeD(?int $liquidaciones, ?string $whereClause = null, array $filters): void
    {
        $sql = "
        SELECT
            dh21.codn_fuent,
            (LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') ||
             LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
             LPAD(dh21.codn_area::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_progr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_subpr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_proye::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_activ::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_final::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
            dh21.nro_legaj,
            dh21.nro_cargo,
            dh21.nro_liqui,
            COALESCE(
                CASE
                    WHEN dh35.tipo_carac = 'T' THEN
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtote, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtote, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtote, 1, 1)::INT
                        END
                    ELSE
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtope, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtope, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtope, 1, 1)::INT
                        END
                END, 1) AS nro_inciso,
            SUM(dh21.impp_conce)::NUMERIC(10, 2) AS netos_d
        INTO TABLE {$this->schema}.rep_ger_importes_netos_d
        FROM mapuche.dh21 dh21
        JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
        LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
        LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
        LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
        LEFT OUTER JOIN mapuche.dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
        WHERE TRUE
        AND dh21.tipo_conce = 'D'  AND dh21.nro_orimp > 0
        AND dh21.nro_liqui = ". $liquidaciones ."
        GROUP BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
        ORDER BY dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso";

        $queryBuilder = new QueryBuilder($sql);
        $finalSql = $this->addFilters($queryBuilder->getSql(), $whereClause);
        $bindings = $this->getBindings($filters);

        DB::connection($this->connection)->statement($finalSql, $bindings);
    }

    /**
     * Procesa los importes netos de tipo A para el reporte gerencial final.
     *
     * Este método ejecuta una consulta SQL que calcula los importes netos de tipo A
     * y los almacena en la tabla `rep_ger_importes_netos_a`. La consulta realiza
     * varios joins con tablas del esquema `mapuche` para obtener la información
     * necesaria.
     *
     * @param array $liquidaciones Array de números de liquidación a procesar.
     * @return void
     */
    protected function processNetAmountsTypeA(?int $liquidaciones, ?string $whereClause = null, array $filters): void
    {
        $sql = "
        SELECT
            dh21.codn_fuent,
            (LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') ||
             LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
             LPAD(dh21.codn_area::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_progr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_subpr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_proye::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_activ::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_final::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
            dh21.nro_legaj,
            dh21.nro_cargo,
            dh21.nro_liqui,
            COALESCE(
                CASE
                    WHEN dh35.tipo_carac = 'T' THEN
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtote, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtote, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtote, 1, 1)::INT
                        END
                    ELSE
                        CASE
                            WHEN ((SUBSTR(dh17.objt_gtope, 1, 1)::INT < 1) OR (SUBSTR(dh17.objt_gtope, 1, 1)::INT > 5))
                            THEN 1
                            ELSE SUBSTR(dh17.objt_gtope, 1, 1)::INT
                        END
                END, 1) AS nro_inciso,
            SUM(dh21.impp_conce)::NUMERIC(15, 2) AS netos_a
        INTO TABLE {$this->schema}.rep_ger_importes_netos_a
        FROM mapuche.dh21 dh21
        JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
        LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
        LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
        LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
        LEFT OUTER JOIN mapuche.dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
        WHERE TRUE
        AND dh21.tipo_conce = 'A'  AND dh21.nro_orimp > 0
        AND dh21.nro_liqui = ". $liquidaciones ."
        GROUP BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui, nro_inciso
        ORDER BY dh21.nro_legaj, dh21.nro_cargo, dh21.codn_fuent, codn_imput, dh21.nro_liqui, nro_inciso";

        $queryBuilder = new QueryBuilder($sql);
        $finalSql = $this->addFilters($queryBuilder->getSql(), $whereClause);
        $bindings = $this->getBindings($filters);

        DB::connection($this->connection)->statement($finalSql, $bindings);
    }

    /** Consolida los importes netos de los diferentes conceptos de pago en una tabla de resumen.
     *
     * Esta función ejecuta una consulta SQL compleja que combina datos de varias tablas relacionadas para calcular los importes netos de diferentes * conceptos de pago (como remuneraciones, aportes, descuentos, etc.) y almacenarlos en una tabla de resumen llamada `rep_ger_importes_netos`.
     * La consulta utiliza múltiples uniones izquierdas para traer los datos de las tablas de importes netos específicas para cada concepto de pago, y luego realiza los cálculos necesarios para obtener los valores finales de importes brutos, netos, aportes, etc.
     *
     * Esta función es parte de la lógica de negocio del servicio `RepGerencialFinalService` y se utiliza para generar informes gerenciales.
     */
    protected function consolidateNetAmounts(): void
    {
        $sql = "
        SELECT
            db.nro_legaj,
            db.nro_cargo,
            db.codn_fuent,
            db.codn_imput,
            db.nro_liqui,
            db.nro_inciso,
            COALESCE(c.netos_c, 0.00) + COALESCE(s.netos_s, 0.00) + COALESCE(o.netos_o, 0.00) + COALESCE(f.netos_f, 0.00) + COALESCE(a.netos_a, 0.00) AS imp_gasto,
            COALESCE(c.netos_c, 0.00) + COALESCE(s.netos_s, 0.00) + COALESCE(o.netos_o, 0.00) + COALESCE(f.netos_f, 0.00) AS imp_bruto,
            COALESCE(c.netos_c, 0.00) + COALESCE(s.netos_s, 0.00) + COALESCE(o.netos_o, 0.00) + COALESCE(f.netos_f, 0.00) - COALESCE(d.netos_d, 0.00) AS imp_neto,
            COALESCE(d.netos_d, 0.00) AS imp_dctos,
            COALESCE(a.netos_a, 0.00) AS imp_aport,
            COALESCE(f.netos_f, 0.00) AS imp_familiar,
            COALESCE(c.netos_c, 0.00) AS rem_c_apor,
            COALESCE(o.netos_o, 0.00) AS otr_no_rem,
            COALESCE(s.netos_s, 0.00) AS rem_s_apor
        INTO TABLE {$this->schema}.rep_ger_importes_netos
        FROM {$this->schema}.rep_ger_datos_base_dh21 db
        LEFT JOIN {$this->schema}.rep_ger_importes_netos_a a ON (
            a.nro_legaj = db.nro_legaj AND
            a.nro_cargo = db.nro_cargo AND
            a.codn_fuent = db.codn_fuent AND
            a.codn_imput = db.codn_imput AND
            a.nro_liqui = db.nro_liqui AND
            a.nro_inciso = db.nro_inciso
        )
        LEFT JOIN {$this->schema}.rep_ger_importes_netos_c c ON (
            c.nro_legaj = db.nro_legaj AND
            c.nro_cargo = db.nro_cargo AND
            c.codn_fuent = db.codn_fuent AND
            c.codn_imput = db.codn_imput AND
            c.nro_liqui = db.nro_liqui AND
            c.nro_inciso = db.nro_inciso
        )
        LEFT JOIN {$this->schema}.rep_ger_importes_netos_o o ON (
            o.nro_legaj = db.nro_legaj AND
            o.nro_cargo = db.nro_cargo AND
            o.codn_fuent = db.codn_fuent AND
            o.codn_imput = db.codn_imput AND
            o.nro_liqui = db.nro_liqui AND
            o.nro_inciso = db.nro_inciso
        )
        LEFT JOIN {$this->schema}.rep_ger_importes_netos_s s ON (
            s.nro_legaj = db.nro_legaj AND
            s.nro_cargo = db.nro_cargo AND
            s.codn_fuent = db.codn_fuent AND
            s.codn_imput = db.codn_imput AND
            s.nro_liqui = db.nro_liqui AND
            s.nro_inciso = db.nro_inciso
        )
        LEFT JOIN {$this->schema}.rep_ger_importes_netos_f f ON (
            f.nro_legaj = db.nro_legaj AND
            f.nro_cargo = db.nro_cargo AND
            f.codn_fuent = db.codn_fuent AND
            f.codn_imput = db.codn_imput AND
            f.nro_liqui = db.nro_liqui AND
            f.nro_inciso = db.nro_inciso
        )
        LEFT JOIN {$this->schema}.rep_ger_importes_netos_d d ON (
            d.nro_legaj = db.nro_legaj AND
            d.nro_cargo = db.nro_cargo AND
            d.codn_fuent = db.codn_fuent AND
            d.codn_imput = db.codn_imput AND
            d.nro_liqui = db.nro_liqui AND
            d.nro_inciso = db.nro_inciso
        )
        ORDER BY db.nro_legaj, db.nro_cargo, db.codn_fuent, db.codn_imput, db.nro_liqui, db.nro_inciso";

        DB::connection($this->connection)->statement($sql);
    }

    protected function processAntiguedad(?int $liquidaciones, ?string $whereClause = null, array $filters): void
    {
        $sql = "
        SELECT
            dh21.codn_fuent,
            (LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') ||
             LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
             LPAD(dh21.codn_area::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_progr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_subpr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_proye::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_activ::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_final::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
            dh21.nro_legaj,
            dh21.nro_cargo,
            dh21.nro_liqui,
            MAX(dh21.nov1_conce) AS ano_antig,
            MAX(dh21.nov2_conce) AS mes_antig
        INTO TABLE {$this->schema}.rep_ger_datos_antiguedad
        FROM mapuche.dh21 dh21
        JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
        LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
        LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
        LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
        LEFT OUTER JOIN mapuche.dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
        WHERE TRUE
        AND dh21.codn_conce = (SELECT dato_parametro
                                FROM mapuche.rrhhini
                                WHERE nombre_seccion = 'Conceptos'
                                AND nombre_parametro = 'Antiguedad')::INT
        AND dh21.nov1_conce < dh21.nov2_conce
        AND (((TRUNC(dh21.nov2_conce / 12)) = dh21.nov1_conce)
            OR ((TRUNC(dh21.nov2_conce / 12) - 1) = dh21.nov1_conce)
            OR ((TRUNC(dh21.nov2_conce / 12) + 1) = dh21.nov1_conce))
        AND (TRUNC(dh21.nov1_conce) < 100)
        AND dh21.nro_liqui = ". $liquidaciones ."
        GROUP BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui
        ORDER BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui";

        $queryBuilder = new QueryBuilder($sql);
        $finalSql = $this->addFilters($queryBuilder->getSql(), $whereClause);
        $bindings = $this->getBindings($filters);

        DB::connection($this->connection)->statement($finalSql, $bindings);
    }

    protected function processHorasTrabajadas(?int $liquidaciones, ?string $whereClause = null, array $filters): void
    {
        $sql = "
        SELECT DISTINCT
            dh21.codn_fuent,
            (LPAD(dh21.tipo_ejercicio::VARCHAR, 1, '0') ||
             LPAD(dh21.codn_grupo_presup::VARCHAR, 4, '0') ||
             LPAD(dh21.codn_area::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_subsubar::VARCHAR, 3, '0') ||
             LPAD(dh21.codn_progr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_subpr::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_proye::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_activ::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_obra::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_final::VARCHAR, 2, '0') ||
             LPAD(dh21.codn_funci::VARCHAR, 2, '0'))::VARCHAR(28) AS codn_imput,
            dh21.nro_legaj,
            dh21.nro_cargo,
            dh21.nro_liqui,
            MAX(dh21.nov1_conce) AS dias_trab,
            MAX(dh21.nov2_conce) AS hs_catedra
        INTO TABLE {$this->schema}.rep_ger_horas_trabajadas
        FROM mapuche.dh21 dh21
        JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
        LEFT OUTER JOIN mapuche.dh01 ON (dh01.nro_legaj = dh21.nro_legaj)
        LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
        LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
        LEFT OUTER JOIN mapuche.dh11 ON (dh03.codc_categ = dh11.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh11.codc_dedic = dh31.codc_dedic)
        LEFT OUTER JOIN mapuche.dh35 ON (dh35.tipo_escal = dh21.tipoescalafon AND dh35.codc_carac = dh03.codc_carac)
        WHERE TRUE
        AND dh21.nro_orimp > 0
        AND dh21.nro_liqui = ". $liquidaciones ."
        GROUP BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui
        ORDER BY dh21.codn_fuent, codn_imput, dh21.nro_legaj, dh21.nro_cargo, dh21.nro_liqui";

        $queryBuilder = new QueryBuilder($sql);
        $finalSql = $this->addFilters($queryBuilder->getSql(), $whereClause);
        $bindings = $this->getBindings($filters);

        DB::connection($this->connection)->statement($finalSql, $bindings);
    }

    protected function appendToFinalReport(): void
    {
        $sql = "
        INSERT INTO {$this->schema}.rep_ger_final (
            codn_fuent, codn_depen, tipo_ejercicio, codn_grupo_presup,
            codn_area, codn_subar, codn_subsubar, codn_progr,
            codn_subpr, codn_proye, codn_activ, codn_obra,
            codn_final, codn_funci, codn_imput, imputacion,
            nro_inciso, nro_legaj, desc_apyno, cant_anios,
            ano_antig, mes_antig,
            nro_cargo, codc_categ, codc_dedic, tipo_escal,
            codc_carac, codc_uacad, codc_regio, fecha_alta,
            fecha_baja, porc_imput, imp_gasto, imp_bruto,
            imp_neto, imp_dctos, imp_aport, imp_familiar,
            ano_liqui, mes_liqui, nro_liqui, tipo_estad,
            cuil, hs_catedra, dias_trab, rem_c_apor,
            otr_no_rem, en_banco, coddependesemp, porc_aplic,
            cod_clasif_cargo, tipo_carac, rem_s_apor
        )
        SELECT DISTINCT
            db.codn_fuent,
            db.codn_depen,
            LPAD(db.tipo_ejercicio::VARCHAR, 1, '0') AS tipo_ejercicio,
            LPAD(db.codn_grupo_presup::VARCHAR, 4, '0') AS codn_grupo_presup,
            LPAD(db.codn_depen::VARCHAR, 3, '0') AS codn_area,
            LPAD(db.codn_subar::VARCHAR, 3, '0') AS codn_subar,
            LPAD(db.codn_subsubar::VARCHAR, 3, '0') AS codn_subsubar,
            LPAD(db.codn_progr::VARCHAR, 2, '0') AS codn_progr,
            LPAD(db.codn_subpr::VARCHAR, 2, '0') AS codn_subpr,
            LPAD(db.codn_proye::VARCHAR, 2, '0') AS codn_proye,
            LPAD(db.codn_activ::VARCHAR, 2, '0') AS codn_activ,
            LPAD(db.codn_obra::VARCHAR, 2, '0') AS codn_obra,
            LPAD(db.codn_final::VARCHAR, 2, '0') AS codn_final,
            LPAD(db.codn_funci::VARCHAR, 2, '0') AS codn_funci,
            LPAD(db.codn_imput::VARCHAR, 28, '0') AS codn_imput,
            (LPAD(db.tipo_ejercicio::VARCHAR, 1, '0') ||'-'|| LPAD(db.codn_grupo_presup::VARCHAR, 3, '0') ||'-'||
            LPAD(db.codn_depen::varchar,3, '0')||'.'||LPAD(db.codn_subar::varchar,3, '0')||'.'||LPAD(db.codn_subsubar::varchar,3, '0')||'-'||
            LPAD(db.codn_fuent::varchar,2, '0')||'-'||
            LPAD(db.codn_progr::varchar,2, '0')||'.'||LPAD(db.codn_subpr::varchar,2, '0')||'.'||LPAD(db.codn_proye::varchar,2, '0')||'.'||
            LPAD(db.codn_activ::varchar,2, '0')||'.'|| LPAD(db.codn_obra::varchar,2, '0')||'-'||
            LPAD(db.codn_final::varchar,2, '0')||'.'||LPAD(db.codn_funci::varchar,2, '0')) AS imputacion,
            db.nro_inciso,
            db.nro_legaj,
            dh01.desc_appat || ', ' || dh01.desc_nombr AS desc_apyno,
            mapuche.map_get_edad('2024-10-31'::DATE, dh01.fec_nacim) AS cant_anios,
            COALESCE(da.ano_antig, 0.00) AS ano_antig,
            COALESCE(da.mes_antig, 0.00) AS mes_antig,
            db.nro_cargo,
            db.codc_categ,
            CASE WHEN db.codigoescalafon = 'NODO' THEN db.codc_agrup
                 ELSE dh11.codc_dedic END AS codc_dedic,
            db.tipo_escal,
            db.codc_carac,
            db.codc_uacad,
            db.codc_regio,
            db.fecha_alta,
            db.fecha_baja,
            db.porc_imput,
            COALESCE(netos.imp_gasto, 0.00) AS imp_gasto,
            COALESCE(netos.imp_bruto, 0.00) AS imp_bruto,
            COALESCE(netos.imp_neto, 0.00) AS imp_neto,
            COALESCE(netos.imp_dctos, 0.00) AS imp_dctos,
            COALESCE(netos.imp_aport, 0.00) AS imp_aport,
            COALESCE(netos.imp_familiar, 0.00) AS imp_familiar,
            db.ano_liqui,
            db.mes_liqui,
            db.nro_liqui,
            dh01.tipo_estad,
            (dh01.nro_cuil1::VARCHAR || LPAD(dh01.nro_cuil::VARCHAR, 8, '0') || dh01.nro_cuil2::VARCHAR) AS cuil,
            CASE WHEN (dh31.cant_horas = 0 AND dh31.tipo_horas = 'S') THEN dh03.hs_dedic
                 ELSE dh31.cant_horas END AS hs_catedra,
            COALESCE(dt.dias_trab, 0.00) AS dias_trab,
            COALESCE(netos.rem_c_apor, 0.00) AS rem_c_apor,
            COALESCE(netos.otr_no_rem, 0.00) AS otr_no_rem,
            db.en_banco,
            db.coddependesemp,
            db.porc_aplic,
            db.cod_clasif_cargo,
            db.tipo_carac,
            COALESCE(netos.rem_s_apor, 0.00) AS rem_s_apor
        FROM {$this->schema}.rep_ger_datos_base_dh21 db
        INNER JOIN mapuche.dh01 dh01 ON (dh01.nro_legaj = db.nro_legaj)
        INNER JOIN mapuche.dh03 dh03 ON (dh03.nro_legaj = db.nro_legaj AND dh03.nro_cargo = db.nro_cargo)
        INNER JOIN {$this->schema}.rep_ger_importes_netos netos ON (
            netos.nro_legaj = db.nro_legaj AND
            netos.nro_cargo = db.nro_cargo AND
            netos.codn_fuent = db.codn_fuent AND
            netos.codn_imput = db.codn_imput AND
            netos.nro_liqui = db.nro_liqui AND
            netos.nro_inciso = db.nro_inciso)
        LEFT OUTER JOIN mapuche.dh11 ON (dh11.codc_categ = db.codc_categ)
        LEFT OUTER JOIN mapuche.dh31 ON (dh31.codc_dedic = dh11.codc_dedic)
        LEFT OUTER JOIN {$this->schema}.rep_ger_datos_antiguedad da ON (
            da.nro_legaj = db.nro_legaj AND
            da.nro_cargo = db.nro_cargo AND
            da.nro_liqui = db.nro_liqui AND
            da.codn_imput = db.codn_imput AND
            da.codn_fuent = db.codn_fuent)
        LEFT OUTER JOIN {$this->schema}.rep_ger_horas_trabajadas dt ON (
            dt.nro_legaj = db.nro_legaj AND
            dt.nro_cargo = db.nro_cargo AND
            dt.codn_fuent = db.codn_fuent AND
            dt.codn_imput = db.codn_imput AND
            dt.nro_liqui = db.nro_liqui)
        ORDER BY db.nro_liqui, db.codn_depen, codn_subar, codn_subsubar, db.codn_fuent,
                 codn_progr, codn_subpr, codn_proye, codn_activ, codn_obra, codn_final,
                 codn_funci, db.tipo_escal";

        DB::connection($this->connection)->statement($sql);
    }
}
