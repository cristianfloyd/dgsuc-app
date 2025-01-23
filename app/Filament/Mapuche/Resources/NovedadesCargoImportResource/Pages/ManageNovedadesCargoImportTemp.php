<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;

use Filament\Resources\Pages\ManageRecords;
use App\Services\NovedadesCargoImportTempService;
use App\Filament\Mapuche\Resources\NovedadesCargoImportResource;

class ManageNovedadesCargoImportTemp extends ManageRecords
{
    protected static string $resource = NovedadesCargoImportResource::class;
    // Si no deseas usar "ManageRecords" y un CRUD completo, podrías usar ListRecords

    public function mount(): void
    {
        parent::mount();

        try {
            // Invocamos el servicio que crea la tabla temporal
            $tempService = new NovedadesCargoImportTempService();
            $tempService->createTempTable();
        } catch (\Throwable $e) {
            $this->notify('danger', 'Error al crear tabla temporal: '.$e->getMessage());
        }
    }
}
