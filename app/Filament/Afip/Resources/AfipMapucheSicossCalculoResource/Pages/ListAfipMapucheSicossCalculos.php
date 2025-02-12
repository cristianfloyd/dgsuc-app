<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Services\AfipMapucheSicossCalculoTableService;
use App\Services\AfipMapucheSicossCalculoUpdateService;
use App\Services\TableManager\TableInitializationManager;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource;
use App\Repositories\Contracts\AfipMapucheSicossCalculoRepository;

class ListAfipMapucheSicossCalculos extends ListRecords
{
    protected static string $resource = AfipMapucheSicossCalculoResource::class;

    public function mount(): void
    {
        $manager = app(TableInitializationManager::class);
        $service = app(AfipMapucheSicossCalculoTableService::class);

        try {
            if (!$manager->isTableInitialized($service)) {
                $manager->initializeTable($service);
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    protected function getHeaderActions(): array
{
    return [
        Action::make('updateFromSicoss')
            ->label('Actualizar desde SICOSS')
            ->icon('heroicon-o-arrow-path')
            ->button()
            ->color('success')
            ->requiresConfirmation()
            ->action(fn () => app(AfipMapucheSicossCalculoUpdateService::class)->updateFromSicoss(date('Ym'))),
        Action::make('truncateTable')
            ->label('Vaciar Tabla')
            ->icon('heroicon-o-trash')
            ->button()
            ->color('danger')
            ->requiresConfirmation()
            ->action(function() {
                app(AfipMapucheSicossCalculoRepository::class)->truncate();
                Notification::make()
                    ->success()
                    ->title('Tabla vaciada')
                    ->body('Se han eliminado todos los registros correctamente')
                    ->send();
            }),
    ];
}

}
