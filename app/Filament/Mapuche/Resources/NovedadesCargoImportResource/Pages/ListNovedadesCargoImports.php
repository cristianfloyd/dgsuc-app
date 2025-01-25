<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;

use Filament\Actions;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Services\NovedadesCargoImportTableService;
use App\Filament\Mapuche\Resources\NovedadesCargoImportResource;

class ListNovedadesCargoImports extends ListRecords
{
    protected static string $resource = NovedadesCargoImportResource::class;

    public NovedadesCargoImportTableService $service;
    public Collection $importData;



    public function mount(): void
    {
        parent::mount();



        // inicializamos la colección de datos vacia
        $this->importData = collect();
        try {
            // Creamos la tabla si no existe

            $this->getService()->createTable();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al crear tabla')
                ->body($e->getMessage())
                ->danger()
                ->send();
        };
    }

    
    #[Computed]
    public function getService():  NovedadesCargoImportTableService
    {
        return app(NovedadesCargoImportTableService::class);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
