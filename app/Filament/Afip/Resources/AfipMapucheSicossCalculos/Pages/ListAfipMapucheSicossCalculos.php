<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossCalculos\Pages;

use App\Filament\Afip\Resources\AfipMapucheSicossCalculos\AfipMapucheSicossCalculos\AfipMapucheSicossCalculoResource;
use App\Repositories\Contracts\AfipMapucheSicossCalculoRepository;
use App\Services\AfipMapucheSicossCalculoTableService;
use App\Services\AfipMapucheSicossCalculoUpdateService;
use App\Services\TableManager\TableInitializationManager;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListAfipMapucheSicossCalculos extends ListRecords
{
    protected static string $resource = AfipMapucheSicossCalculoResource::class;

    #[Override]
    public function mount(): void
    {
        $tableInitializationManager = resolve(TableInitializationManager::class);
        $afipMapucheSicossCalculoTableService = resolve(AfipMapucheSicossCalculoTableService::class);

        try {
            if (!$tableInitializationManager->isTableInitialized($afipMapucheSicossCalculoTableService)) {
                $tableInitializationManager->initializeTable($afipMapucheSicossCalculoTableService);
            }
        } catch (Exception $e) {
            report($e);
        }
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            // Action::make('updateUacadAndCaracter')
            //     ->label('Actualizar UA/CAD y Carácter')
            //     ->icon('heroicon-o-arrow-path')
            //     ->button()
            //     ->color('warning')
            //     ->requiresConfirmation()
            //     ->action(function() {
            //         $result = app(AfipMapucheSicossCalculoUpdateService::class)->updateUacadAndCaracter();

            //         if ($result['success']) {
            //             Notification::make()
            //                 ->success()
            //                 ->title('Actualización exitosa')
            //                 ->body($result['message'])
            //                 ->send();
            //         } else {
            //             Notification::make()
            //                 ->danger()
            //                 ->title('Error en la actualización')
            //                 ->body($result['message'])
            //                 ->send();
            //         }
            //     }),
            Action::make('truncateTable')
                ->label('Vaciar Tabla')
                ->icon('heroicon-o-trash')
                ->button()
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    resolve(AfipMapucheSicossCalculoRepository::class)->truncate();
                    Notification::make()
                        ->success()
                        ->title('Tabla vaciada')
                        ->body('Se han eliminado todos los registros correctamente')
                        ->send();
                }),
        ];
    }
}
