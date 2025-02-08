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
        return " ";
    }


    /**
     * Método para sincronizar datos específicos de un período
     */
    public function syncPeriodo(string $periodoFiscal): void
    {
        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            // Eliminar registros existentes del período
            DB::connection($this->getConnectionName())->table($this->getTableName())
                ->where('periodo_fiscal', $periodoFiscal)
                ->delete();

            // Ejecutar query de población con parámetro
            DB::connection($this->getConnectionName())->statement($this->getTablePopulationQuery(), [
                'periodo_fiscal' => $periodoFiscal
            ]);

            DB::connection($this->getConnectionName())->commit();

            Log::info("Sincronización SICOSS completada", [
                'periodo' => $periodoFiscal,
                'tabla' => $this->getTableName()
            ]);
        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();
            Log::error("Error en sincronización SICOSS", [
                'periodo' => $periodoFiscal,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
