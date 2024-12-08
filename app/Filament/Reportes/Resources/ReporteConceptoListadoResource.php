<?php

namespace App\Filament\Reportes\Resources;

use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use App\Services\Dh12Service;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reportes\ConceptoListado;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Services\ConceptoListadoResourceService;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\EditReporteConceptoListado;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\ListReporteConceptoListados;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\CreateReporteConceptoListado;

class ReporteConceptoListadoResource extends Resource
{
    protected static ?string $model = ConceptoListado::class;
    protected static ?string $modelLabel = 'Listado de Concepto';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Reportes';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('desc_liqui')->toggleable(),
                TextColumn::make('periodo_fiscal')
                    ->label('Periodo')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->periodo_fiscal),
                TextColumn::make('codc_uacad')->label('dependencia'),
                TextColumn::make('nro_legaj')->sortable(),
                TextColumn::make('cuil')->label('CUIL'),
                TextColumn::make('desc_appat')->label('Apellido'),
                TextColumn::make('desc_nombr')->label('Nombre'),
                TextColumn::make('secuencia')->label('Secuencia'),
                TextColumn::make('codn_conce')->label('Concepto'),
                TextColumn::make('impp_conce')->label('Importe'),
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
                SelectFilter::make('codn_conce')
                    ->label('Concepto')
                    ->multiple()
                    ->options(Dh12Service::getConceptosParaSelect())
                    ->searchable(),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filtro'),
            )
            ->actions([
                //
            ])
            ->bulkActions([
                //
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
            'create' => CreateReporteConceptoListado::route('/create'),
            'edit' => EditReporteConceptoListado::route('/{record}/edit'),
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        return app(ConceptoListadoResourceService::class)
            ->getFilteredQuery(request()->get('tableFilters', []));
    }


}
