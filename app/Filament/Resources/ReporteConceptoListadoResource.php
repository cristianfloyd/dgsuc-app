<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use App\Services\Dh12Service;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reportes\ConceptoListado;
use App\Services\ConceptoListadoService;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\EditReporteConceptoListado;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\ListReporteConceptoListados;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\CreateReporteConceptoListado;

class ReporteConceptoListadoResource extends Resource
{
    protected static ?string $model = ConceptoListado::class;
    protected static ?string $modelLabel = 'Concepto Listado';

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Reportes';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function boot(): void
    {
        Log::info('Mounting ReporteConceptoListadoResource');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')->label('id'),
                TextColumn::make('codc_uacad')->label('dependencia'),
                TextColumn::make('periodo_fiscal')
                    ->label('Periodo')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->periodo_fiscal)
                    ->searchable(),
                // TextColumn::make('nro_liqui')->label('Nro. Liq.')->toggleable()->toggledHiddenByDefault()
                //     ->sortable(),
                TextColumn::make('desc_liqui')->toggleable(),
                TextColumn::make('nro_legaj')->sortable(),
                TextColumn::make('cuil')->label('CUIL')->searchable(),
                TextColumn::make('desc_appat')->label('Apellido'),
                TextColumn::make('desc_nombr')->label('Nombre'),
                TextColumn::make('coddependesemp')->label('Oficina de Pago'),
                TextColumn::make('secuencia')->label('Secuencia'),
                TextColumn::make('codn_conce')->label('Concepto'),
                TextColumn::make('tipo_conce')->label('Tipo'),
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
        $service = app(ConceptoListadoService::class);
        $concepto = request()->input('tableFilters.codn_conce');

        $query = $service->getQueryForConcepto($concepto);
        return $query;

    }


}
