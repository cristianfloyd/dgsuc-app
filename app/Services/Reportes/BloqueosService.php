<?php

namespace App\Services\Reportes;

use Carbon\Carbon;
use App\Exceptions\ImportValidationException;
use App\Services\Imports\BloqueosImportService;
use App\Services\Reportes\Interfaces\BloqueosServiceInterface;

class BloqueosService implements BloqueosServiceInterface
{
    protected BloqueosImportService $importService;
    protected BloqueosProcessService $processService;

    public function __construct(
        BloqueosImportService $importService,
        BloqueosProcessService $processService
    ) {
        $this->importService = $importService;
        $this->processService = $processService;
    }

    public function processImport(array $data): array
    {
        // Delega la importación inicial
        $importedData = $this->importService->processRow($data);

        // Procesa los datos importados
        return $this->processService->procesarBloqueos($importedData);
    }

    public function processRow(array $row): array
    {
        // Lógica para procesar una fila individual
        return $this->processService->procesarFila($row);
    }
}
