<?php

namespace App\Services\Reportes;

use App\Data\Reportes\BloqueoProcesadoData;
use App\Data\Reportes\BloqueosData;
use App\Services\Imports\BloqueosImportService;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BloqueosService implements BloqueosServiceInterface
{
    public function __construct(
        protected BloqueosImportService $importService,
        protected BloqueosProcessService $processService,
        private readonly int $nroLiqui,
    ) {
        Log::debug("numero de liquidacion en BloqueoService: {$this->nroLiqui}");
    }

    /**
     * Procesa una fila de datos de bloqueos importados desde un archivo de Excel.
     *
     * @param array $row Fila de datos de bloqueos importados desde Excel.
     *
     * @return Collection<int, BloqueoProcesadoData> Colección de resultados del procesamiento de bloqueos.
     */
    public function processImport(array|BloqueosData $row): Collection
    {
        Log::debug('Procesando fila de bloqueos: ' . json_encode($row));
        // transformamos primero a DTO para garantizar la integridad de los datos
        $bloqueosData = BloqueosData::fromExcelRow($row, $this->nroLiqui);

        // Procesamos usando el DTO validado
        return $this->processService->procesarBloqueos($bloqueosData);
    }

    public function processRow(array $row): array
    {
        // Lógica para procesar una fila individual
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function validateData(array $data): bool
    {
        return true;
    }
}
