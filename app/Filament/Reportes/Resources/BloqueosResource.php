<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\BloqueosEstadoEnum;
use App\Exports\FallecidosExport;
use Filament\Actions\ActionGroup;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Filament\Support\Enums\MaxWidth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use App\Exports\BloqueosResultadosExport;
use Filament\Forms\Components\FileUpload;
use App\Models\Reportes\BloqueosDataModel;
use App\Services\Reportes\BloqueosProcessService;
use App\Services\Reportes\BloqueosValidationService;
use App\Livewire\Filament\Reportes\Components\BloqueosProcessor;
use App\Filament\Reportes\Resources\BloqueosResource\Pages\ImportData;
use App\Filament\Reportes\Resources\BloqueosResource\Pages\ViewBloqueo;
use App\Filament\Reportes\Resources\BloqueosResource\Pages\EditImportData;
use App\Filament\Reportes\Resources\BloqueosResource\Pages\ListImportData;
use App\Filament\Reportes\Resources\BloqueosResource\RelationManagers\CargosRelationManager;

class BloqueosResource extends Resource
{
    protected static ?string $model = BloqueosDataModel::class;
    protected static ?string $label = 'Bloqueos';
    protected static ?string $pluralLabel = 'Bloqueos';
    protected static ?string $navigationGroup = 'Informes';
    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?Collection $resultadosProcesamiento = null;

    public $excel_file = null;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        FileUpload::make('excel_file')
                            ->label('Archivo Excel')
                            ->preserveFilenames()
                            ->directory('imports')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                            ])
                            ->maxSize(5120)
                            ->downloadable()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableColums())
            ->filters(self::getTableFilters())
            ->recordClasses(self::getRecordClasses())
            ->actions(self::getTableActions())
            ->bulkActions(self::getTableBulkActions())
            ->headerActions(self::getTableHeaderActions())
            ->deferLoading();
    }

    protected static function getResultados(): Collection
    {
        $cacheKey = 'bloqueos_resultados_' . auth()->guard('web')->id();
    
        // Invalidar caché cuando sea necesario
        if (session()->has('invalidate_bloqueos_cache')) {
            Cache::forget($cacheKey);
            session()->forget('invalidate_bloqueos_cache');
        }
        
        return Cache::remember($cacheKey, 3600, function () {
            $service = app(BloqueosProcessService::class);
            return $service->procesarBloqueos();
        });
    }

    public static function getRelations(): array
    {
        return [
            CargosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImportData::route('/'),
            'create' => ImportData::route('/crear'),
            'view' => ViewBloqueo::route('/{record}'),
            'edit' => EditImportData::route('/{record}/editar'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return 'Nuevo';
    }

    /**
     * Procesa los bloqueos y prepara la vista de resultados
     *
     * @return \Illuminate\View\View
     */
    protected static function procesarYMostrarResultados()
    {
        try {
            $service = app(BloqueosProcessService::class);
            $resultados = $service->procesarBloqueos();
            
            return view('filament.resources.bloqueos.bulk-results', [
                'resultados' => $resultados,
                'exitosos' => $resultados->where('estado', 'Procesado')->count(),
                'fallidos' => $resultados->where('estado', 'Error')->count(),
                'resumen' => static::generarResumenProcesamiento($resultados),
                'total_procesados' => BloqueosDataModel::where('esta_procesado', true)->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al procesar bloqueos: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            Notification::make()
                ->title('Error al procesar bloqueos')
                ->body('Se ha producido un error inesperado. Por favor, contacte al administrador.')
                ->danger()
                ->send();
                
            return view('filament.resources.bloqueos.error', [
                'mensaje' => 'Error al procesar los bloqueos'
            ]);
        }
    }

    /**
     * Genera un resumen estadístico del procesamiento
     *
     * @param Collection $resultados
     * @return array
     */
    protected static function generarResumenProcesamiento($resultados): array
    {
        return [
            'total' => $resultados->count(),
            'por_tipo' => $resultados->groupBy('tipo_bloqueo')
                ->map(fn($grupo) => $grupo->count()),
            'porcentaje_exito' => $resultados->where('estado', 'Procesado')->count() * 100 / $resultados->count(),
            'total_procesados_historico' => BloqueosDataModel::where('esta_procesado', true)->count(),
            'total_pendientes' => BloqueosDataModel::where('esta_procesado', false)->count(),
        ];
    }

    protected function exportarResultados(Collection $resultados)
    {
        return Excel::download(
            new BloqueosResultadosExport($resultados),
            'resultados_bloqueos_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    private static function getTableColums(): array
    {
        return [
            TextColumn::make('estado')
                ->badge()
                ->color(fn(BloqueosEstadoEnum $state): string => $state->getColor()),
            TextColumn::make('nro_liqui')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('fecha_registro')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('nombre')->toggleable(isToggledHiddenByDefault: true)->copyable(),
            TextColumn::make('email')->toggleable(isToggledHiddenByDefault: true)->copyable(),
            TextColumn::make('usuario_mapuche')->toggleable(isToggledHiddenByDefault: true)->copyable(),
            TextColumn::make('dependencia')->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('nro_legaj')->searchable()->copyable(),
            TextColumn::make('nro_cargo')->searchable()->copyable(),
            TextColumn::make('cargo.codc_uacad')->label('Uacad')->searchable()->sortable(),
            TextColumn::make('fecha_baja')->date('Y-m-d')->sortable(),
            TextColumn::make('cargo.fec_baja')->label('Fecha dh03')->date('Y-m-d')->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('tipo')->searchable(),
            TextColumn::make('observaciones')
                ->limit(20)
                ->tooltip(function (TextColumn $column): ?string {
                    return $column->getState();
                })->toggleable(isToggledHiddenByDefault: true),
            IconColumn::make('chkstopliq')->boolean(),
            TextColumn::make('mensaje_error')
                ->limit(20)
                ->tooltip(function (TextColumn $column): ?string {
                    return $column->getState();
                })->toggleable(),
            IconColumn::make('tiene_cargo_asociado')
                ->label('Asociado')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger'),
            IconColumn::make('esta_procesado')
                ->label('Procesado')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger'),
            TextColumn::make('cargoAsociado.nro_cargoasociado')
                ->label('Cargo Asociado')
                ->tooltip('Número de cargo asociado')
                ->toggleable(isToggledHiddenByDefault: true)
                ->searchable(),
            TextColumn::make('cargoAsociado.tipoasociacion')
                ->label('Tipo Asociación')
                ->badge()
                ->color(fn($state) => match ($state) {
                    'S' => 'success',
                    'R' => 'warning',
                    default => 'gray',
                })
                ->toggleable(),
            TextColumn::make('cargoAsociado.nro_cargoasociado')
                ->label('Info Cargo Asociado')
                ->formatStateUsing(
                    fn($record) =>
                    $record->cargoAsociado
                        ? "Cargo: {$record->cargoAsociado->nro_cargoasociado}
                           (Tipo: {$record->cargoAsociado->tipoasociacion})"
                        : 'Sin cargo asociado'
                )
                ->tooltip('Información completa del cargo asociado')
                ->toggleable()
                ->searchable(),
        ];
    }

    private static function getTableFilters(): array
    {
        return [
            Filter::make('con_cargo_asociado')
                ->label('Con Cargo Asociado')
                ->query(fn($query) => $query->whereHas('cargoAsociado'))
                ->toggle(),
            Filter::make('fechas_coincidentes')
                ->label('Fechas Coincidentes')
                ->query(fn($query) => $query->whereRaw('fecha_baja = (SELECT fec_baja FROM dh03 WHERE dh03.nro_legaj = rep_bloqueos_import.nro_legaj AND dh03.nro_cargo = rep_bloqueos_import.nro_cargo)'))
                ->toggle(),
            Filter::make('licencia_bloqueada')
                ->label('Licencia Bloqueada')
                ->query(fn($query) => $query->where('tipo', 'licencia')->where('estado', BloqueosEstadoEnum::LICENCIA_YA_BLOQUEADA))
                ->toggle(),
            Filter::make('falta_cargo_asociado')
                ->label('Falta Cargo Asociado')
                ->query(fn($query) => $query->where('estado', BloqueosEstadoEnum::FALTA_CARGO_ASOCIADO))
                ->toggle(),
            Filter::make('fecha_cargo_no_coincide')
                ->label('Fecha Cargo No Coincide')
                ->query(fn($query) => $query->where('estado', BloqueosEstadoEnum::FECHA_CARGO_NO_COINCIDE))
                ->toggle(),
            Filter::make('ocultar_procesados')
                ->label('Ocultar Procesados')
                ->query(fn($query) => $query->where('esta_procesado', false))
                ->toggle()
                ->default(true),
            Filter::make('solo_procesados')
                ->label('Solo Procesados')
                ->query(fn($query) => $query->where('esta_procesado', true))
                ->toggle(),
        ];
    }

    private static function getRecordClasses(): callable
    {

        $clase = fn(BloqueosDataModel $record): string =>
        match (true) {
            $record->fechas_coincidentes => 'bg-green-50 dark:bg-green-900/50 !border-l-4 !border-l-green-900 !dark:border-l-green-900',
            $record->tipo === 'licencia' => 'bg-blue-50 dark:bg-blue-900/50 !border-l-4 !border-l-blue-900 !dark:border-l-blue-900',
            $record->estado === BloqueosEstadoEnum::FALTA_CARGO_ASOCIADO => 'bg-orange-50 dark:bg-orange-900/50 !border-l-4 !border-l-orange-900 !dark:border-l-orange-900',
            $record->estado === BloqueosEstadoEnum::FECHA_CARGO_NO_COINCIDE => 'bg-red-50 dark:bg-red-900/50 !border-l-4 !border-l-red-900 !dark:border-l-red-900',
            $record->esta_procesado => 'bg-gray-50 dark:bg-gray-900/50 !border-l-4 !border-l-gray-900 !dark:border-l-gray-900',
            default => ''
        };
        return $clase;
    }

    private static function getTableActions(): array
    {
        return [
            Action::make('edit')
                ->label('Editar')
                ->icon('heroicon-o-pencil-square')
                ->url(fn(BloqueosDataModel $record): string =>
                static::getUrl('edit', ['record' => $record]))
                ->color('warning'),
            Action::make('validar')
                ->label('Validar')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('¿Validar registro?')
                ->modalDescription('Se validará el par legajo-cargo contra Mapuche y se verificarán los cargos asociados.')
                ->action(function ($record) {
                    try {
                        // Usamos el servicio de validación
                        $validationService = app(BloqueosValidationService::class);
                        $resultado = $validationService->validarRegistro($record);
                        
                        // Mostramos la notificación con el resultado
                        Notification::make()
                            ->title($resultado['mensaje'])
                            ->color($resultado['color'])
                            ->send();
                    } catch (\Exception $e) {
                        // Manejo de excepciones no esperadas
                        Notification::make()
                            ->title('Error al validar el registro')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
        ];
    }

    private static function getTableBulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->action(function (Collection $records): void {
                    $records->each(function ($record) {
                        $record->delete();
                    });
                })
                ->label('Eliminar'),
            // Acción masiva para múltiples registros
            BulkAction::make('procesarBloqueos')
                ->label('Procesar Seleccionados')
                ->icon('heroicon-o-arrow-path')
                ->modalContent(fn($records): View => view('filament.resources.bloqueos-resource.process-modal', [
                    'registros' => $records
                ]))
                ->modalWidth(MaxWidth::FiveExtraLarge)
                ->modalHeading('Procesamiento de Bloqueos')
                ->modalSubmitAction(false)
                ->modalCancelAction(false),

            BulkAction::make('exportarResultados')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function (Collection $records) {
                    $count = $records->count();

                    // Opcional: Verificar que hay registros seleccionados
                    if ($count === 0) {
                        Notification::make()
                            ->title('No hay registros seleccionados')
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title("Exportando $count registros")
                        ->success()
                        ->send();

                    return Excel::download(
                        new BloqueosResultadosExport($records),
                        'resultados_bloqueos_seleccionados_' . now()->format('Y-m-d_His') . '.xlsx'
                    );
                }),

            BulkAction::make('validar_registros')
                ->label('Validar Registros')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('¿Validar registros seleccionados?')
                ->modalDescription('Se validará el par legajo-cargo de cada registro contra Mapuche y se verificarán los cargos asociados.')
                ->action(function (Collection $records) {
                    try {
                        // Usamos el servicio de validación para procesar múltiples registros
                        $validationService = app(BloqueosValidationService::class);
                        $estadisticas = $validationService->validarMultiplesRegistros($records);
                        
                        // Generamos el mensaje de resumen
                        $mensajeResumen = $validationService->generarMensajeResumen($estadisticas);
                        
                        // Mostramos la notificación con el resultado
                        Notification::make()
                            ->title('Validación completada')
                            ->body($mensajeResumen)
                            ->success()
                            ->send();
                            
                        // Si hubo errores generales, mostramos una notificación adicional
                        if (isset($estadisticas['error_general'])) {
                            Notification::make()
                                ->title('Advertencia')
                                ->body('Ocurrieron algunos errores durante el proceso: ' . $estadisticas['error_general'])
                                ->warning()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        // Manejo de excepciones no esperadas
                        Notification::make()
                            ->title('Error al validar los registros')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->deselectRecordsAfterCompletion()
        ];
    }

    public function afterSave(): void
    {
        session()->put('invalidate_bloqueos', true);
    }

    private static function getTableHeaderActions(): array
    {
        return [
            Action::make('export_fallecidos')
                ->label('Exportar Fallecidos')
                ->icon('heroicon-o-document-arrow-down')
                ->form([
                    Forms\Components\Select::make('periodo_fiscal')
                        ->label('Periodo Fiscal')
                        ->options(app(\App\Services\Mapuche\PeriodoFiscalService::class)->getPeriodosFiscalesForSelect())
                        ->required()
                        ->default(now()->format('Ym'))
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state) {
                            $set('periodo', $state);
                        })
                ])
                ->action(function (array $data) {
                    $periodoFiscal = $data['periodo_fiscal'];
                    // Obtener registros de fallecidos por bloqueos
                    $registrosBloqueos = static::$model::query()
                        ->where('tipo', 'fallecido')
                        ->with(['dh01','cargo'])
                        ->get();


                    // Obtener registros de fallecidos del sistema
                    $registrosRep = \App\Models\RepFallecido::query()
                        ->get();

                    if (!$registrosBloqueos && !$registrosRep) {
                        Notification::make()
                            ->title('No hay registros de fallecidos para exportar')
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Exportando registros de fallecidos')
                        ->body('El archivo contendrá dos hojas: Fallecidos por Bloqueos y Fallecidos del Sistema')
                        ->success()
                        ->send();

                    return Excel::download(
                        new FallecidosExport(
                            $registrosBloqueos,
                            $periodoFiscal
                        ),
                        'fallecidos_consolidado_' . now()->format('Y-m-d_His') . '.xlsx'
                    );
                })
        ];
    }
}
