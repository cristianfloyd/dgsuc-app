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
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Illuminate\Support\HtmlString;

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
            Actions\CreateAction::make()
                ->label('Importar datos')
                ->icon('heroicon-o-arrow-down-tray')
                ->tooltip('Importar datos desde el archivo Excel')
                ->color('info'),
            Action::make('validar_todos')
                ->label('Validar Todos')
                ->tooltip('Validar todos los registros contra Mapuche')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('¿Validar todos los registros?')
                ->modalDescription('Se validarán todos los registros contra Mapuche. Esta operación puede tomar tiempo.')
                ->action(function () {
                    $registros = BloqueosDataModel::all();
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
                        ->body("Total procesados: {$total}<br> Validados: {$validados}<br>Con error: {$conError}")
                        ->success()
                        ->send();
                }),
            Action::make('validar_cargos_asociados')
                ->label('Validar Cargos Asociados')
                ->tooltip('Verificar si cada registro tiene un cargo asociado en Mapuche')
                ->icon('heroicon-o-link')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('¿Validar cargos asociados en Mapuche?')
                ->modalDescription('Esta acción verificará si cada registro tiene un cargo asociado en Mapuche.')
                ->action(function () {
                    try {
                        $service = app(ValidacionCargoAsociadoService::class);
                        $estadisticas = $service->validarCargosAsociados();

                        Notification::make()
                            ->title('Validación de cargos completada')
                            ->body("Total: {$estadisticas['total']}<br>Con cargo: {$estadisticas['con_cargo']}<br>Sin cargo: {$estadisticas['sin_cargo']}")
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
                ->tooltip('Procesar los registros validados')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('¿Procesar bloqueos?')
                ->modalDescription('Esta acción realizará los siguientes pasos:

1. Creará un respaldo de la tabla DH03 actual
2. Filtrará los registros validados correctamente
3. Para cada registro validado:
   - Actualizará o creará el bloqueo en DH03
   - Registrará la fecha de proceso
   - Actualizará el estado del registro
4. Generará un resumen de las operaciones realizadas

Nota: Solo se procesarán los registros en estado "validado".')
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
            Action::make('procesar_duplicados')
                ->label('Procesar Duplicados')
                ->color('warning')
                ->icon('heroicon-o-document-duplicate')
                ->requiresConfirmation()
                ->modalHeading('¿Procesar registros duplicados?')
                ->modalDescription('Esta acción procesará los registros duplicados siguiendo estas reglas:
                    1. Identificará grupos de registros con el mismo par legajo-cargo
                    2. Para cada grupo:
                       - Procesará solo el registro más antiguo
                       - Marcará el resto como duplicados y los eliminará
                    3. Generará un respaldo de los datos originales
                    4. Solo se procesarán los duplicados si el par legajo-cargo existe en Mapuche
                    ¿Desea continuar?')
                ->action(function () {
                    try {
                        $service = new BloqueosProcessService();
                        $service->procesarBloqueosDuplicados();

                        Notification::make()
                            ->title('Duplicados procesados exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al procesar duplicados')
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
            Action::make('documentation')
                ->label('Documentación')
                ->icon('heroicon-o-book-open')
                ->color('secondary')
                ->modalHeading('Documentación de Bloqueos')
                ->modalWidth('7xl')
                ->modalContent(function () {
                    $markdown = file_get_contents(resource_path('docs/documentacion_bloqueos_resource.md'));

                    $environment = new Environment();
                    $environment->addExtension(new CommonMarkCoreExtension());
                    $environment->addExtension(new GithubFlavoredMarkdownExtension());

                    $converter = new MarkdownConverter($environment);
                    $html = $converter->convert($markdown)->getContent();

                    return view('filament.documentation-modal', [
                        'documentacionHtml' => $html
                    ]);
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
                ->badge(fn() => $this->getModel()::where('tipo', 'licencia')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'licencia')),
            'Fallecido' => Tab::make()
                ->badge(fn() => $this->getModel()::where('tipo', 'fallecido')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'fallecido')),
            'Renuncia' => Tab::make()
                ->badge(fn() => $this->getModel()::where('tipo', 'renuncia')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', 'renuncia')),
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
