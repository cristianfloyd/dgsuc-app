<?php

namespace App\Filament\Reportes\Resources\BloqueosResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Enums\BloqueosTipoEnum;
use App\Enums\BloqueosEstadoEnum;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Components\Tab;
use App\Services\ImportDataTableService;
use Filament\Notifications\Notification;
use League\CommonMark\MarkdownConverter;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Reportes\BloqueosDataModel;
use App\Services\Reportes\BloqueosService;
use App\Services\Reportes\BloqueosDataService;
use League\CommonMark\Environment\Environment;
use App\Services\Reportes\BloqueosProcessService;
use App\Services\Reportes\BloqueosValidationService;
use App\Filament\Reportes\Resources\BloqueosResource;
use App\Services\Reportes\ValidacionCargoAsociadoService;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use App\Filament\Reportes\Resources\BloqueosResource\Widgets\ColorReferenceWidget;

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
            Action::make('validar_todo_completo')
                ->label('Validar Todo')
                ->tooltip('Valida todos los registros y verifica cargos asociados en Mapuche')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('¿Validar todos los registros y cargos asociados?')
                ->modalDescription('Se validarán todos los registros contra Mapuche y se verificará si cada uno tiene un cargo asociado. Esta operación puede tomar tiempo.')
                ->action(function (
                    BloqueosValidationService $validationService,
                    ValidacionCargoAsociadoService $cargoService
                ) {
                    try {
                        // 1. Validar todos los registros
                        $estadisticasValidacion = $validationService->validarTodosLosRegistros();

                        // 2. Validar cargos asociados
                        $estadisticasCargos = $cargoService->validarCargosAsociados();

                        // 3. Notificación combinada
                        Notification::make()
                            ->title('Validación completa')
                            ->body(
                                "Validación masiva:<br>
                                Total procesados: {$estadisticasValidacion['total']}<br>
                                Validados: {$estadisticasValidacion['validados']}<br>
                                Con error: {$estadisticasValidacion['conError']}<br><br>
                                Validación de cargos:<br>
                                Total: {$estadisticasCargos['total']}<br>
                                Con cargo: {$estadisticasCargos['con_cargo']}<br>
                                Sin cargo: {$estadisticasCargos['sin_cargo']}"
                            )
                            ->success()
                            ->send();
                    } catch (\Throwable $th) {
                        Log::error('Error en la validación completa', [
                            'error' => $th->getMessage(),
                            'trace' => $th->getTraceAsString(),
                            'user_id' => auth()->guard('web')->user()->id,
                        ]);
                        Notification::make()
                            ->title('Error en la validación')
                            ->body('Error: ' . $th->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('procesar_todo')
                ->label('Procesar Todo')
                ->tooltip('Procesa los bloqueos validados y los duplicados en un solo paso')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('¿Procesar bloqueos y duplicados?')
                ->modalDescription('Esta acción procesará los bloqueos validados y luego los duplicados, generando un resumen de ambas operaciones.')
                ->action(function (BloqueosProcessService $service) {
                    try {
                        // Procesar bloqueos validados
                        $resBloqueos = $service->procesarBloqueos();

                        // Procesar duplicados
                        $resDuplicados = $service->procesarBloqueosDuplicados();

                        // Notificación combinada
                        Notification::make()
                            ->title('Procesamiento completo')
                            ->body(
                                "Bloqueos procesados:<br>" .
                                "Total procesados: " . ($resBloqueos['procesados'] ?? '-') . "<br>" .
                                "Errores: " . ($resBloqueos['errores'] ?? '0') . "<br>" .
                                "<br>" .
                                "Duplicados procesados:<br>" .
                                "Grupos detectados: " . ($resDuplicados['grupos'] ?? '-') . "<br>" .
                                "Registros eliminados: " . ($resDuplicados['eliminados'] ?? '-')
                            )
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Error al procesar bloqueos y duplicados', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'user_id' => auth()->guard('web')->user()->id,
                        ]);
                        Notification::make()
                            ->title('Error en el procesamiento')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('restaurar')
                ->label('Restaurar Cambios')
                ->tooltip('Revertir los últimos cambios realizados en DH03 por el usuario actual')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-left')
                ->requiresConfirmation()
                ->modalHeading('¿Restaurar cambios en DH03?')
                ->modalDescription('Esta acción revertirá los últimos cambios realizados en la tabla DH03.')
                ->action(function (BloqueosProcessService $service) {
                    try {
                        $service->restaurarBackup();

                        Notification::make()
                            ->title('Cambios restaurados exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Log::error('Error al restaurar cambios', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'user_id' => auth()->guard('web')->user()->id,
                        ]);
                        Notification::make()
                            ->title('Error al restaurar cambios')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('truncate')
                ->label('Eliminar')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('¿Vaciar tabla de bloqueos?')
                ->modalDescription('Esta acción eliminará todos los registros de la tabla. Esta operación no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, vaciar tabla')
                ->action(function (BloqueosDataService $service) {
                    try {
                        $service->truncateTable();

                        Notification::make()
                            ->title('Tabla vaciada exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Log::error('Error al vaciar la tabla', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'user_id' => auth()->guard('web')->user()->id,
                        ]);
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
                ->color('primary')
                ->modalHeading('Documentación de Bloqueos')
                ->modalWidth('7xl')
                ->modalContent(function () {
                    $markdown = file_get_contents(resource_path('docs/documentacion_bloqueos_resource.md'));

                    $environment = new Environment();
                    $environment->addExtension(new CommonMarkCoreExtension());
                    $environment->addExtension(new GithubFlavoredMarkdownExtension());

                    $converter = new MarkdownConverter($environment);
                    $html = $converter->convert($markdown)->getContent();

                    // Procesamiento adicional para mejorar la estructura

                    // 1. Mejorar el estilo de las notas
                    $html = preg_replace(
                        '/<blockquote>\s*<p><strong>Nota.*?<\/strong>(.*?)<\/p>\s*<\/blockquote>/s',
                        '<div class="bg-primary-50/50 dark:bg-primary-900/20 p-4 rounded-lg border-l-4 border-primary-500 my-4">
                            <p class="flex items-start m-0">
                                <svg class="w-5 h-5 text-primary-500 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Nota:</strong>$1</span>
                            </p>
                        </div>',
                        $html
                    );

                    // 2. Mejorar secciones importantes
                    $html = str_replace(
                        '<h2>Mejores Prácticas</h2>',
                        '<div class="mt-8 mb-4">
                            <h2 class="flex items-center text-success-600 dark:text-success-400">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Mejores Prácticas
                            </h2>
                        </div>',
                        $html
                    );

                    $html = str_replace(
                        '<h2>Resolución de Problemas Comunes</h2>',
                        '<div class="mt-8 mb-4">
                            <h2 class="flex items-center text-warning-600 dark:text-warning-400">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Resolución de Problemas Comunes
                            </h2>
                        </div>',
                        $html
                    );

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
                ->badge(fn() => $this->getModel()::count()),
            'valido' => Tab::make()
                ->badge(fn() => $this->getModel()::where('estado', BloqueosEstadoEnum::VALIDADO->value)->count())
                ->badgeColor(BloqueosEstadoEnum::VALIDADO->getColor())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('estado', BloqueosEstadoEnum::VALIDADO->value)),
            'Duplicados' => Tab::make()
                ->badge(fn() => $this->getModel()::where('estado', BloqueosEstadoEnum::DUPLICADO->value)->count())
                ->badgeColor(BloqueosEstadoEnum::DUPLICADO->getColor())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('estado', BloqueosEstadoEnum::DUPLICADO->value)),
            'Licencia' => Tab::make()
                ->badge(fn() => $this->getModel()::where('tipo', BloqueosTipoEnum::LICENCIA->value)->count())
                ->badgeColor(BloqueosTipoEnum::LICENCIA->getColor())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', BloqueosTipoEnum::LICENCIA->value)),
            'Fallecido' => Tab::make()
                ->badge(fn() => $this->getModel()::where('tipo', BloqueosTipoEnum::FALLECIDO->value)->count())
                ->badgeColor(BloqueosTipoEnum::FALLECIDO->getColor())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', BloqueosTipoEnum::FALLECIDO->value)),
            'Renuncia' => Tab::make()
                ->badge(fn() => $this->getModel()::where('tipo', BloqueosTipoEnum::RENUNCIA->value)->count())
                ->badgeColor(BloqueosTipoEnum::RENUNCIA->getColor())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipo', BloqueosTipoEnum::RENUNCIA->value)),
            'error_validacion' => Tab::make()
                ->badge(fn() => $this->getModel()::where('estado', BloqueosEstadoEnum::ERROR_VALIDACION->value)->count())
                ->badgeColor(BloqueosEstadoEnum::ERROR_VALIDACION->getColor())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('estado', BloqueosEstadoEnum::ERROR_VALIDACION->value)),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }
}
