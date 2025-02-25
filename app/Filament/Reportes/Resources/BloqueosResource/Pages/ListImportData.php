<?php

namespace App\Filament\Reportes\Resources\BloqueosResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Enums\BloqueosEstadoEnum;
use Filament\Resources\Components\Tab;
use App\Services\ImportDataTableService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Reportes\BloqueosDataModel;
use App\Services\Reportes\BloqueosDataService;
use App\Services\Reportes\BloqueosProcessService;
use App\Filament\Reportes\Resources\BloqueosResource;
use App\Filament\Reportes\Resources\BloqueosResource\Widgets\ColorReferenceWidget;
use App\Services\Reportes\BloqueosService;
use App\Services\Reportes\ValidacionCargoAsociadoService;

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
            Action::make('validar_todos')
                ->label('Validar Todos')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('¿Validar todos los registros?')
                ->modalDescription('Se validarán todos los registros contra Mapuche. Esta operación puede tomar tiempo.')
                ->action(function () {
                    $registros = BloqueosDataModel::all()->where('estado', '!=', BloqueosEstadoEnum::DUPLICADO);
                    $total = $registros->count();
                    $validados = 0;
                    $conError = 0;

                    foreach ($registros as $registro) {
                        $registro->validarEstado();

                        if ($registro->estado === BloqueosEstadoEnum::VALIDADO) {
                            $validados++;
                        } else {
                            $conError++;
                        }
                    }

                    Notification::make()
                        ->title('Validación masiva completada')
                        ->body("Total procesados: {$total}\nValidados: {$validados}\nCon error: {$conError}")
                        ->success()
                        ->send();
                }),
                Action::make('validar_cargos_asociados')
                ->label('Validar Cargos en Mapuche')
                ->icon('heroicon-o-link')
                ->requiresConfirmation()
                ->modalHeading('¿Validar cargos en Mapuche?')
                ->modalDescription('Esta acción verificará si cada registro tiene un cargo asociado en Mapuche.')
                ->action(function () {
                    try {
                        $service = app(ValidacionCargoAsociadoService::class);
                        $estadisticas = $service->validarCargosAsociados();

                        Notification::make()
                            ->title('Validación de cargos completada')
                            ->body("Total: {$estadisticas['total']}\nCon cargo: {$estadisticas['con_cargo']}\nSin cargo: {$estadisticas['sin_cargo']}")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error en la validación')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('procesar')
                ->label('Procesar Bloqueos')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('¿Procesar bloqueos?')
                ->modalDescription('Esta acción actualizará los registros en la tabla DH03.')
                ->action(function () {
                    try {
                        $service = new BloqueosProcessService();
                        $service->procesarBloqueos();

                        Notification::make()
                            ->title('Bloqueos procesados exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al procesar bloqueos')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('restaurar')
                ->label('Restaurar Cambios')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-left')
                ->requiresConfirmation()
                ->modalHeading('¿Restaurar cambios en DH03?')
                ->modalDescription('Esta acción revertirá los últimos cambios realizados en la tabla DH03.')
                ->action(function () {
                    try {
                        $service = new BloqueosProcessService();
                        $service->restaurarBackup();

                        Notification::make()
                            ->title('Cambios restaurados exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al restaurar cambios')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
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
                }),
            
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todo' => Tab::make()
                ->badge(fn() => $this->getModel()::all()->count()),
            'Duplicados' => Tab::make()
                ->badge(fn() => $this->getModel()::where('estado', 'duplicado')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('estado', 'duplicado')),
            'Licencia' => Tab::make()
                ->badge(fn() => $this->getModel()::where('tipo', 'licencia')->where('estado','validado')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'licencia')->where('estado','validado')),
            'Fallecido' => Tab::make()
                ->badge(fn() => $this->getModel()::where('tipo', 'fallecido')->where('estado','validado')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'fallecido')->where('estado','validado')),
            'Renuncia' => Tab::make()
                ->badge(fn() => $this->getModel()::where('tipo', 'renuncia')->where('estado','validado')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'renuncia')->where('estado','validado')),
            'valido' => Tab::make()
                ->badge(fn() => $this->getModel()::where('estado', 'validado')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('estado', 'validado')),
            'error_validacion' => Tab::make()
                ->badge(fn() => $this->getModel()::where('estado', 'error_validacion')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('estado', 'error_validacion')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }
}
