<?php

namespace App\Services\Reportes;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Data\Reportes\BloqueosData;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ImportValidationException;
use App\Services\Imports\BloqueosImportService;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;

class BloqueosService implements BloqueosServiceInterface
{
    protected BloqueosImportService $importService;
    protected BloqueosProcessService $processService;

    public function __construct(
        BloqueosImportService $importService,
        BloqueosProcessService $processService,
        private readonly int $nroLiqui
    ) {
        $this->importService = $importService;
        $this->processService = $processService;
        Log::debug("numero de liquidacion en BloqueoService: {$this->nroLiqui}");
    }


    /**
     * Procesa una fila de datos de bloqueos importados desde un archivo de Excel.
     *
     * @param array $row Fila de datos de bloqueos importados desde Excel.
     * @return \Illuminate\Support\Collection<BloqueosData> Colección de datos de bloqueos procesados.
     */
    public function processImport(array|BloqueosData $row): Collection
    {
        Log::debug("Procesando fila de bloqueos: " . json_encode($row));
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
     * @inheritDoc
     */
    public function validateData(array $data): bool
    {
        return true;
    }
}
