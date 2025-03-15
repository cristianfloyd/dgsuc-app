<?php

namespace App\Filament\Reportes\Resources;

use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use App\Services\Dh12Service;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reportes\ConceptoListado;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\ConceptoListadoResourceService;
use App\Services\ConceptoListado\ConceptoListadoSyncService;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\ListReporteConceptoListados;

class ReporteConceptoListadoResource extends Resource
{
    protected static ?string $model = ConceptoListado::class;
    protected static ?string $modelLabel = 'Listado Concepto';
    protected static ?string $slug = 'listado-concepto';
    protected static ?string $navigationGroup = 'Informes';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')->label('Liquidación'),
                TextColumn::make('desc_liqui')->toggleable(),
                TextColumn::make('nro_legaj')->sortable()->searchable(),
                TextColumn::make('nro_cargo')->label('Secuencia')->toggleable(),
                TextColumn::make('codc_uacad')->label('dependencia'),
                TextColumn::make('apellido')->label('Apellido'),
                TextColumn::make('nombre')->label('Nombre'),
                TextColumn::make('cuil')->label('CUIL')
                    ->toggleable(),
                TextColumn::make('periodo_fiscal')
                    ->label('Periodo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->getStateUsing(fn($record) => $record->periodo_fiscal),
                TextColumn::make('codn_conce')->label('Concepto'),
                TextColumn::make('impp_conce')->label('Importe'),
            ])
            ->filters([
                Filter::make('periodo_liquidacion')
                    ->form([
                        Select::make('periodo_fiscal')
                            ->label('Periodo')
                            ->options(fn() => app(PeriodoFiscalService::class)->getPeriodosFiscalesForSelect())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('nro_liqui', null)),

                        Select::make('nro_liqui')
                            ->label('Liquidación')
                            ->options(function (callable $get): array {
                                $periodoString = $get('periodo_fiscal');
                                if (!$periodoString) return [];

                                [$year, $month] = explode('-', $periodoString);
                                return app(PeriodoFiscalService::class)->getLiquidacionesByPeriodo($year, $month);
                            })
                            ->searchable()
                            ->live()
                            ->disabled(fn(callable $get): bool => !$get('periodo_fiscal')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['nro_liqui'],
                                fn(Builder $query, int $nroLiqui) => $query->withLiquidacion($nroLiqui),
                            );
                    }),
                SelectFilter::make('codn_conce')
                    ->label('Concepto')
                    ->multiple()
                    ->options(Dh12Service::getConceptosParaSelect())
                    ->searchable(),
            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filtro'),
            )
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ])
            ->headerActions([
                Action::make('regenerar_reporte')
                    ->label('Regenerar Reporte')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Select::make('periodo_fiscal')
                            ->label('Período Fiscal')
                            ->options(fn () => app(PeriodoFiscalService::class)->getPeriodosFiscalesForSelect())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('nro_liqui', null)),

                        Select::make('nro_liqui')
                            ->label('Liquidación (opcional)')
                            ->options(function (callable $get): array {
                                $periodoString = $get('periodo_fiscal');
                                if (!$periodoString) return [];

                                [$year, $month] = explode('-', $periodoString);
                                return app(PeriodoFiscalService::class)->getLiquidacionesByPeriodo($year, $month);
                            })
                            ->searchable()
                            ->placeholder('Todas las liquidaciones')
                            ->disabled(fn (callable $get): bool => !$get('periodo_fiscal')),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Regenerar datos del reporte')
                    ->modalDescription('Esta acción sincronizará los datos desde Mapuche para el periodo actual. Puede tardar varios minutos dependiendo del volumen de datos.')
                    ->modalSubmitActionLabel('Sí, regenerar')
                    ->modalIcon('heroicon-o-arrow-path')
                    ->action(function (array $data) {
                        try {
                            $periodoFiscal = $data['periodo_fiscal'];
                            $nroLiqui = $data['nro_liqui'] ?? null;

                            // Mostrar notificación de inicio
                            Notification::make()
                                ->title('Sincronización iniciada')
                                ->body("Regenerando reporte para el período {$periodoFiscal}" .
                                      ($nroLiqui ? " y liquidación #{$nroLiqui}" : ""))
                                ->info()
                                ->persistent()
                                ->send();

                            // Ejecutar sincronización
                            $syncService = app(ConceptoListadoSyncService::class);
                            $registrosInsertados = $syncService->sync($periodoFiscal, $nroLiqui);

                            // Mostrar notificación de éxito
                            Notification::make()
                                ->success()
                                ->title('Reporte Regenerado')
                                ->body("Se han sincronizado {$registrosInsertados} registros exitosamente para el período {$periodoFiscal}.")
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('refresh')
                                        ->label('Actualizar página')
                                        ->button()
                                        ->url(request()->fullUrl())
                                ])
                                ->persistent()
                                ->send();
                        } catch (\Exception $e) {
                            // Mostrar notificación de error
                            Notification::make()
                                ->danger()
                                ->title('Error al regenerar reporte')
                                ->body('Ocurrió un error: ' . $e->getMessage())
                                ->persistent()
                                ->send();

                            // Registrar error en logs
                            \Illuminate\Support\Facades\Log::error('Error al regenerar reporte de conceptos', [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                                'periodo_fiscal' => $periodoFiscal,
                                'nro_liqui' => $nroLiqui
                            ]);
                        }
                    }),

                Action::make('limpiar_cache')
                    ->label('Limpiar Caché')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        try {
                            if (config('cache.default') === 'redis') {
                                Cache::tags(['concepto_listado'])->flush();
                            } else {
                                Cache::flush(); // Fallback para otros drivers
                            }

                            Notification::make()
                                ->success()
                                ->title('Caché Limpiada')
                                ->body('La caché ha sido limpiada exitosamente.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Error al limpiar la caché: ' . $e->getMessage())
                                ->send();
                        }
                    })
            ])
            ->deferLoading()
            ->persistFiltersInSession()
            ->defaultPaginationPageOption(5)
            ->reorderable(false)
            ->paginationPageOptions([5, 10, 25, 50, 100])
            ->emptyStateHeading('Seleccione un concepto')
            ->emptyStateDescription('Para visualizar los datos, primero debe seleccionar un concepto del filtro superior.')
            ->emptyStateIcon('heroicon-o-funnel')
        ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReporteConceptoListados::route('/'),
            // 'create' => CreateReporteConceptoListado::route('/create'),
            //'edit' => EditReporteConceptoListado::route('/{record}/edit'),
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        return app(ConceptoListadoResourceService::class)
            ->getFilteredQuery(request()->get('tableFilters', []));
    }
}
