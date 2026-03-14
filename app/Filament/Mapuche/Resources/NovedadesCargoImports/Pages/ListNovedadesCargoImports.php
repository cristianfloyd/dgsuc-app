<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImports\Pages;

use App\Filament\Mapuche\Resources\NovedadesCargoImports\NovedadesCargoImports\NovedadesCargoImportResource;
use App\Services\NovedadesCargoImportTableService;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Throwable;

class ListNovedadesCargoImports extends ListRecords
{
    public NovedadesCargoImportTableService $service;

    public Collection $importData;

    protected static string $resource = NovedadesCargoImportResource::class;

    #[\Override]
    public function mount(): void
    {
        parent::mount();

        // inicializamos la colección de datos vacia
        $this->importData = collect();
        try {
            // Creamos la tabla si no existe

            $this->getService()->createTable();

        } catch (Throwable $e) {
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
        return resolve(NovedadesCargoImportTableService::class);
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
