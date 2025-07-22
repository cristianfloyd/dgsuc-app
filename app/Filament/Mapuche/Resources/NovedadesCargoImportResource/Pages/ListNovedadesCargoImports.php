<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;

use App\Filament\Mapuche\Resources\NovedadesCargoImportResource;
use App\Services\NovedadesCargoImportTableService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

class ListNovedadesCargoImports extends ListRecords
{
    public NovedadesCargoImportTableService $service;

    public Collection $importData;

    protected static string $resource = NovedadesCargoImportResource::class;

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
        }
    }

    #[Computed]
    public function getService(): NovedadesCargoImportTableService
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
