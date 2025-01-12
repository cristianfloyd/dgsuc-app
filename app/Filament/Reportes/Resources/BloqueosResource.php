<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\BloqueosEstadoEnum;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\View\View;
use Filament\Support\Enums\MaxWidth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Exports\BloqueosResultadosExport;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Reportes\BloqueosDataModel;
use App\Services\Reportes\BloqueosProcessService;
use App\Filament\Reportes\Resources\Bloqueos\Pages;
use App\Livewire\Filament\Reportes\Components\BloqueosProcessor;
use App\Filament\Reportes\Resources\BloqueosResource\Pages\ViewBloqueo;
use App\Filament\Reportes\Resources\BloqueosResource\RelationManagers\CargosRelationManager;

class BloqueosResource extends Resource
{
    protected static ?string $model = BloqueosDataModel::class;
    protected static ?string $label = 'Bloqueos';
    protected static ?string $pluralLabel = 'Bloqueos';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            ->columns([
                TextColumn::make('nro_liqui')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fecha_registro')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nombre')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('usuario_mapuche')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dependencia')->sortable(),
                TextColumn::make('nro_legaj'),
                TextColumn::make('nro_cargo'),
                TextColumn::make('fecha_baja')->date('Y-m-d')->sortable(),
                TextColumn::make('cargo.fec_baja')->label('Fecha dh03'),
                TextColumn::make('tipo')->searchable(),
                TextColumn::make('observaciones')
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string{
                        return $column->getState();
                    }),
                IconColumn::make('chkstopliq')->boolean(),
                TextColumn::make('estado')
                    ->badge()
                    ->colors([
                        'gray' => BloqueosEstadoEnum::IMPORTADO->value,
                        'info' => BloqueosEstadoEnum::VALIDADO->value,
                        'warning' => BloqueosEstadoEnum::ERROR_VALIDACION->value,
                        'success' => BloqueosEstadoEnum::PROCESADO->value,
                        'danger' => BloqueosEstadoEnum::ERROR_PROCESO->value,
                    ]),
                TextColumn::make('mensaje_error')
            ])
            ->filters([
                // Filtro por tipo de bloqueo
                SelectFilter::make('tipo')
                    ->options([
                        'Licencia' => 'licencia',
                        'Fallecido' => 'fallecido',
                        'Renuncia' => 'renuncia',
                    ]),
                // Filtro por estado de procesamiento
                SelectFilter::make('chkstopliq')
                    ->label('Estado')
                    ->options([
                        '1' => 'Procesado',
                        '0' => 'Pendiente',
                    ]),
            ])
            ->recordClasses(fn (BloqueosDataModel $record): string =>
                match(true) {
                    $record->fechas_coincidentes => 'bg-green-50 dark:bg-green-900/50 !border-l-4 !border-l-green-900 !dark:border-l-green-900',
                    $record->tipo === 'licencia' => 'bg-blue-50 dark:bg-blue-900/50 !border-l-4 !border-l-blue-900 !dark:border-l-blue-900',
                    default => ''
                }
            )
            ->actions([
                Action::make('procesarBloqueos')
                    ->label('Procesar Bloqueo')
                    ->icon('heroicon-o-arrow-path')
                    ->modalContent(fn ($records): View => view('filament.resources.bloqueos-resource.process-modal', [
                        'registro' => $records
                    ]))
                    ->modalWidth(MaxWidth::FiveExtraLarge)
                    ->modalHeading('Procesamiento de Bloqueos')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)

            ])
            ->bulkActions([
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
                    ->modalContent(fn ($records): View => view('filament.resources.bloqueos-resource.process-modal', [
                        'registros' => $records
                    ]))
                    ->modalWidth(MaxWidth::FiveExtraLarge)
                    ->modalHeading('Procesamiento de Bloqueos')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),

                BulkAction::make('exportarResultados')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $processor = new BloqueosProcessor();
                        return Excel::download(
                            new BloqueosResultadosExport($processor->getResultados()),
                            'resultados_bloqueos_' . now()->format('Y-m-d_His') . '.xlsx'
                        );
                    })
            ]);
        }

    protected static function getResultados(): Collection
    {
        return Cache::remember('bloqueos_resultados_' . auth()->id(), 3600, function () {
            if (!static::$resultadosProcesamiento) {
                $service = app(BloqueosProcessService::class);
                static::$resultadosProcesamiento = $service->procesarBloqueos();
            }
            return static::$resultadosProcesamiento;
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
            'index' => Pages\ListImportData::route('/'),
            'create' => Pages\ImportData::route('/crear'),
            'view' => ViewBloqueo::route('/{record}'),
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
        $service = app(BloqueosProcessService::class);
        $resultados = $service->procesarBloqueos();

        return view('filament.resources.bloqueos.bulk-results', [
            'resultados' => $resultados,
            'exitosos' => $resultados->where('estado', 'Procesado')->count(),
            'fallidos' => $resultados->where('estado', 'Error')->count(),
            'resumen' => static::generarResumenProcesamiento($resultados)
        ]);
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
                ->map(fn ($grupo) => $grupo->count()),
            'porcentaje_exito' => $resultados->where('estado', 'Procesado')->count() * 100 / $resultados->count()
        ];
    }

    protected function exportarResultados(Collection $resultados)
    {
        return Excel::download(
            new BloqueosResultadosExport($resultados),
            'resultados_bloqueos_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }


}
