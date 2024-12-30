<?php

namespace App\Filament\Reportes\Resources\Bloqueos\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use App\Services\ImportDataTableService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Reportes\BloqueosDataService;
use App\Filament\Reportes\Resources\BloqueosResource;

class ListImportData extends ListRecords
{
    protected static string $resource = BloqueosResource::class;

    public function mount(): void
    {
        app(ImportDataTableService::class)->ensureTableExists();
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Importar datos'),
            Actions\Action::make('truncate')
                ->label('Vaciar Tabla')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('¿Vaciar tabla de bloqueos?')
                ->modalDescription('Esta acción eliminará todos los registros de la tabla. Esta operación no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, vaciar tabla')
                ->action(function () {
                    try {
                        $bloqueosService = new BloqueosDataService();
                        $bloqueosService->truncateTable();

                        Notification::make()
                            ->title('Tabla vaciada exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al vaciar la tabla')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todo' => Tab::make(),
            'Licencia' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'Licencia')),
            'Fallecido' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'Fallecido')),
            'Renuncia' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'Renuncia')),
        ];
    }
}
