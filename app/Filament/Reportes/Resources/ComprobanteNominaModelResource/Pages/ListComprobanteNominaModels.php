<?php

namespace App\Filament\Reportes\Resources\ComprobanteNominaModels\Pages;

use App\Filament\Reportes\Resources\ComprobanteNominaModels\ComprobanteNominaModelResource;
use App\Services\ComprobanteNominaService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Exception;

class ListComprobanteNominaModels extends ListRecords
{
    protected static string $resource = ComprobanteNominaModelResource::class;
    protected ComprobanteNominaService $comprobanteNominaService;

    public function boot(ComprobanteNominaService $comprobanteNominaService): void
    {
        $this->comprobanteNominaService = $comprobanteNominaService;
        Log::debug('LisComprobantesNomina iniciada (booted)');
    }

    public function mount(): void
    {
        if (! $this->comprobanteNominaService->checkTableExists()) {
            $this->comprobanteNominaService->createTable();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('truncateTable')
                ->label('Limpiar Tabla')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('¿Estás seguro de limpiar la tabla?')
                ->modalDescription('Esta acción eliminará todos los registros y reiniciará los índices. No se puede deshacer.')
                ->modalSubmitActionLabel('Sí, limpiar tabla')
                ->modalCancelActionLabel('Cancelar')
                ->action(function (): void {
                    try {
                        $this->comprobanteNominaService->truncateTable();

                        Notification::make()
                            ->title('Tabla limpiada exitosamente')
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Log::error('Error al truncar tabla: '.$e->getMessage());

                        Notification::make()
                            ->title('Error al limpiar la tabla')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
