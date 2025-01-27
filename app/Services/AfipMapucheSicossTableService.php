<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Abstract\AbstractTableService;
use App\Tables\Definitions\AfipMapucheSicossTableDefinition;

class AfipMapucheSicossTableService extends AbstractTableService
{
    private AfipMapucheSicossTableDefinition $definition;

    public function __construct(AfipMapucheSicossTableDefinition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Obtiene la definición de la tabla
     */
    protected function getTableDefinition(): array
    {
        return $this->definition->getColumns();
    }

    

    /**
     * Obtiene los índices de la tabla
     */
    protected function getIndexes(): array
    {
        return $this->definition->getIndexes();
    }

    /**
     * Obtiene el nombre de la tabla
     */
    public function getTableName(): string
    {
        return $this->definition->getTableName();
    }

    /**
     * Query para poblar la tabla desde Mapuche
     */
    protected function getTablePopulationQuery(): string
    {
        return "
        INSERT INTO {$this->getTableName()} (
            nro_legaj,
            cuil,
            apnom,
            conyuge,
            cant_hijos,
            cod_situacion,
            cod_cond,
            cod_act,
            cod_zona,
            porc_aporte,
            cod_mod_cont,
            cod_os,
            cant_adh
        )
        SELECT DISTINCT
            DISTINCT(dh01.nro_legaj),
            (dh01.nro_cuil1::char(2)||LPAD(dh01.nro_cuil::char(8),8,'0')||dh01.nro_cuil2::char(1))::varchar as cuil,
            dh01.desc_appat||' '||dh01.desc_nombr AS apnom,
            (SELECT COUNT(*) > 0
                FROM mapuche.dh02
                WHERE dh02.nro_legaj = dh01.nro_legaj
                AND dh02.sino_cargo!='N'
                AND dh02.codc_paren ='CONY'
            ) AS conyuge,
            (SELECT COUNT(*)
                FROM mapuche.dh02
                WHERE dh02.nro_legaj=dh01.nro_legaj
                AND dh02.sino_cargo!='N'
                AND dh02.codc_paren IN ('HIJO', 'HIJN', 'HINC' ,'HINN' )
            ) AS cant_hijos,
            dha8.codigosituacion,
            dha8.codigocondicion,
            dha8.codigoactividad,
            dha8.codigozona,
            dha8.porcaporteadicss AS porc_aporte,
            dha8.codigomodalcontrat AS cod_mod_cont,
            COALESCE(dh09.codc_obsoc, '000000') as cod_os,
            dh09.cant_cargo AS cant_adh
        FROM mapuche.dh01
        LEFT JOIN mapuche.dh02 ON dh02.nro_legaj = dh01.nro_legaj
        LEFT JOIN mapuche.dha8 ON dha8.nro_legajo = dh01.nro_legaj
        LEFT JOIN mapuche.dh09 ON dh09.nro_legaj = dh01.nro_legaj
        LEFT JOIN mapuche.dhe9 ON dhe9.nro_legaj = dh01.nro_legaj
        WHERE dh01.tipo_estad = 'A'
    ";
    }


    /**
     * Método para sincronizar datos específicos de un período
     */
    public function syncPeriodo(string $periodoFiscal): void
    {
        try {
            DB::beginTransaction();

            // Eliminar registros existentes del período
            DB::table($this->getTableName())
                ->where('periodo_fiscal', $periodoFiscal)
                ->delete();

            // Ejecutar query de población con parámetro
            DB::statement($this->getTablePopulationQuery(), [
                'periodo_fiscal' => $periodoFiscal
            ]);

            DB::commit();

            Log::info("Sincronización SICOSS completada", [
                'periodo' => $periodoFiscal,
                'tabla' => $this->getTableName()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en sincronización SICOSS", [
                'periodo' => $periodoFiscal,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
