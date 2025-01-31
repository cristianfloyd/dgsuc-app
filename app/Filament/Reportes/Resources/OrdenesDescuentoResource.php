<?php

namespace App\Filament\Reportes\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\ConceptoGrupo;
use App\Models\Mapuche\Dh22;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use App\Exports\OrdenesDescuentoExport;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use App\Exports\OrdenesDescuentoSheet200;
use App\Models\Reportes\OrdenesDescuento;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\OrdenAportesyContribuciones;
use App\Services\OrdenesDescuentoTableService;
use App\Exports\OrdenesDescuentoMultipleExport;
use App\Contracts\Tables\OrdenesDescuentoTableDefinition;
use App\Filament\Reportes\Resources\OrdenesDescuentoResource\Pages;

class OrdenesDescuentoResource extends Resource
{
    protected static ?string $model = OrdenesDescuento::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?int $navigationSort = 2;



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nro_liqui'),
                TextColumn::make('desc_liqui')->sortable(),
                TextColumn::make('codc_uacad')->label('UA')->sortable()->searchable(),
                TextColumn::make('desc_item')->label('Dependencia')->sortable()->searchable(),
                TextColumn::make('codn_funci')->sortable(),
                TextColumn::make('caracter')->sortable(),
                TextColumn::make('tipoescalafon')->sortable(),
                TextColumn::make('codn_fuent')->sortable(),
                TextColumn::make('nro_inciso')->sortable(),
                TextColumn::make('codn_progr')->sortable(),
                TextColumn::make('codn_conce')->sortable(),
                TextColumn::make('desc_conce')->label('Descripción Concepto')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('impp_conce')->label('Importe')->sortable()
                    ->money('ARS')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('last_sync')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('periodo_liquidacion')
                        ->form([
                            Select::make('periodo_fiscal')
                                ->label('Periodo')
                                ->options( Dh22::getPeriodosFiscales())
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('nro_liqui', null)),

                            Select::make('nro_liqui')
                                ->label('Liquidación')
                                ->options(function (callable $get): array {
                                    $periodo = $get('periodo_fiscal');
                                    if (!$periodo) return [];

                                    return Dh22::query()
                                        ->WithPeriodoFiscal($periodo)
                                        ->definitiva()
                                        ->pluck('desc_liqui', 'nro_liqui')
                                        ->toArray();
                                })
                                ->searchable()
                                ->live()
                                ->disabled(fn (callable $get): bool => !$get('periodo_fiscal')),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                            ->when(
                                $data['nro_liqui'],
                                fn(Builder $query, int $nroLiqui) => $query->withLiquidacion($nroLiqui),
                            );
                        }),
                SelectFilter::make('codc_uacad')
                    ->label('Unidad Académica')
                    ->options(fn () => OrdenesDescuento::distinct()
                        ->orderBy('codc_uacad')
                        ->pluck('desc_item', 'codc_uacad')
                        ->toArray())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('codn_conce')
                    ->label('Concepto')
                    ->options(fn () => OrdenesDescuento::distinct()
                        ->orderBy('codn_conce')
                        ->pluck('desc_conce', 'codn_conce')
                        ->toArray())
                    ->searchable()
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                //
            ])
            ->headerActions([
                Action::make('populate')
                    ->label('Poblar Tabla')
                    ->tooltip('Sincronizar datos desde la base de datos Mapuche')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('¿Desea sincronizar los datos?')
                    ->modalDescription('Esta acción actualizará los datos desde la base de datos Mapuche. Puede tomar varios minutos.')
                    ->modalSubmitActionLabel('Sí, sincronizar')
                    ->action(function () {
                        $tableService = new OrdenesDescuentoTableService(new OrdenesDescuentoTableDefinition());
                        $tableService->populateTable();

                        Notification::make()
                            ->success()
                            ->title('Tabla poblada exitosamente')
                            ->send();
                    }),
                Action::make('export')
                    ->label('Exportar Todo')
                    ->tooltip('Exportar todos los registros a un archivo Excel en dos hojas')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        return (new OrdenesDescuentoMultipleExport($livewire->getFilteredTableQuery()))
                            ->download('descuentos-y-aportes-' . now()->format('d-m-Y') . '.xlsx');
                    }),
                Action::make('export200')
                    ->label('Ordenes Descuento')
                    ->tooltip('Exportar los conceptos de ordenes de descuento a un archivo Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        return (new OrdenesDescuentoSheet200($livewire->getFilteredTableQuery()->whereBetween('codn_conce', [200, 299])))
                            ->download('ordenes-descuento-' . now()->format('d-m-Y') . '.xlsx');
                    }),
                Action::make('export300')
                    ->label('Aportes y Contrib.')
                    ->tooltip('Exportar Aportes y Contribuciones a un archivo Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        return (new OrdenAportesyContribuciones(
                            $livewire->getFilteredTableQuery()
                            ->whereIn('codn_conce', ConceptoGrupo::APORTES_Y_CONTRIBUCIONES->getConceptos())))
                            ->download('aportes-y-contribuciones-' . now()->format('d-m-Y') . '.xlsx');
                    }),
                Action::make('truncate')
                    ->label('Limpiar Tabla')
                    ->tooltip('Eliminar todos los registros de la tabla')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Está seguro que desea eliminar todos los registros?')
                    ->modalDescription('Esta acción eliminará todos los registros de la tabla y no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar todo')
                    ->action(function () {
                        OrdenesDescuento::truncate();
                        Notification::make()
                            ->success()
                            ->title('Tabla limpiada exitosamente')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ])
            ->deferLoading()
            ->persistFiltersInSession()
            ->striped()
            ->defaultPaginationPageOption(5)
            ->paginatedWhileReordering()
            ->paginationPageOptions([5, 10, 25, 50, 100])
            ->emptyStateHeading('Seleccione un concepto')
            ->emptyStateDescription('Para visualizar los datos, primero debe seleccionar un concepto del filtro superior.')
            ->emptyStateIcon('heroicon-o-funnel')
            ->poll('60s');
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
            'index' => Pages\ListOrdenesDescuentos::route('/'),
            'edit' => Pages\EditOrdenesDescuento::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $service = app()->make(OrdenesDescuentoTableService::class);

        if (!$service->exists()) {
            $service->createAndPopulate();
        }

        return parent::getEloquentQuery();
    }
}
