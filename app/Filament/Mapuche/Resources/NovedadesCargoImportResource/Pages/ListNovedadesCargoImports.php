<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;

use Filament\Actions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Services\NovedadesCargoImportTableService;
use App\Filament\Mapuche\Resources\NovedadesCargoImportResource;

class ListNovedadesCargoImports extends ListRecords
{
    protected static string $resource = NovedadesCargoImportResource::class;

    public $service;
    public Collection $importData;

    public function boot()
    {
        $this->service = new NovedadesCargoImportTableService();
        Log::info("ListNovedadesCargoImports::boot()");
    }

    public function mount(): void
    {
        parent::mount();
        // inicializamos la colección de datos vacia
        $this->importData = collect();
        try {
            // Creamos la tabla si no existe
            $tableService = new NovedadesCargoImportTableService();
            $tableService->createTable();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al crear tabla')
                ->body($e->getMessage())
                ->danger()
                ->send();
        };
    }



    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
