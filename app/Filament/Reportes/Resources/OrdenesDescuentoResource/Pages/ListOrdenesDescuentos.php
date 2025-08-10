<?php

namespace App\Filament\Reportes\Resources\OrdenesDescuentoResource\Pages;

use App\Filament\Reportes\Resources\OrdenesDescuentoResource;
use App\Services\OrdenesDescuentoTableService;
use App\Traits\TableVerificationTrait;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

class ListOrdenesDescuentos extends ListRecords
{
    use TableVerificationTrait;

    protected static string $resource = OrdenesDescuentoResource::class;

    public function mount(): void
    {
        parent::mount();

        if (!$this->verifyAndInitializeTable(OrdenesDescuentoTableService::class)) {
            Notification::make()
                ->title('Error de Inicialización')
                ->body('No se pudo inicializar la tabla')
                ->danger()
                ->persistent()
                ->send();

            $this->redirect(route('filament.admin.pages.dashboard'));
        }

        Notification::make()
            ->title('Tabla inicializada correctamente')
            ->success()
            ->send();
    }

    protected function beforeFill(): void
    {
        Log::info('Antes de llenar la tabla');
        if (!$this->verifyAndInitializeTable(OrdenesDescuentoTableService::class)) {
            Notification::make()
                ->title('Error de Inicialización')
                ->body('No se pudo inicializar la tabla')
                ->danger()
                ->persistent()
                ->send();

            $this->redirect(route('filament.admin.pages.dashboard'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
