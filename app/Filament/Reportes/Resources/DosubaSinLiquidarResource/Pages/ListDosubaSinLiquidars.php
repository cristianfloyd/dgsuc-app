<?php

namespace App\Filament\Reportes\Resources\DosubaSinLiquidarResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Models\Reportes\DosubaSinLiquidarModel;
use App\Filament\Reportes\Resources\DosubaSinLiquidarResource;

class ListDosubaSinLiquidars extends ListRecords
{
    protected static string $resource = DosubaSinLiquidarResource::class;

    public function mount(): void
    {
        parent::mount();

        // Aseguramos que la tabla exista antes de cualquier operación
        DosubaSinLiquidarModel::createTableIfNotExists();
        // Limpiamos registros antiguos
        DosubaSinLiquidarModel::cleanOldRecords();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Generar Reporte'),
            Action::make('vaciarTabla')
                ->label('Vaciar Tabla')
                ->action(function () {
                    DosubaSinLiquidarModel::clearSessionData();
                    Notification::make()->success()->title('La tabla ha sido vaciada exitosamente.')->send();
                })
                ->color('danger') // Puedes cambiar el color según tu preferencia
                ->requiresConfirmation() // Solicita confirmación antes de ejecutar la acción
                ->modalHeading('Confirmar Vaciar Tabla')
                ->modalDescription('¿Estás seguro de que deseas vaciar la tabla? Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Vaciar')
        ];
    }
}
