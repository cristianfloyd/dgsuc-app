<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use App\Services\Dh12Service;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
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
                TextColumn::make('id')->label('id'),
                TextColumn::make('codc_uacad')->label('dep'),
                TextColumn::make('periodo_fiscal')
                    ->label('Periodo')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->periodo_fiscal)
                    ->searchable(),
                TextColumn::make('nro_liqui')->label('Nro. Liq.')->toggleable()->toggledHiddenByDefault()
                    ->sortable(),
                TextColumn::make('desc_liqui')->toggleable()->toggledHiddenByDefault(),
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
                SelectFilter::make('codn_conce')
                    ->label('Concepto')
                    ->multiple()
                    ->options(Dh12Service::getConceptosParaSelect())
                    ->searchable(),
                SelectFilter::make('periodo_fiscal')
                    ->label('Periodo')
                    ->options(Dh22::getPeriodosFiscales())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['value'])) {
                            return $query;
                        }
                        $year = substr($data['value'], 0, 4);
                        $month = substr($data['value'], 4, 2);
                        return $query->where('per_liano', $year)
                            ->where('per_limes', $month);
                    }),
                SelectFilter::make('Nro Liqui')
                ->label('Liquidación')
                ->options(function (Get $get) {
                    $periodo = $get('periodo_fiscal');
                    if (!$periodo) return [];

                    return Dh22::query()->where('per_liano', substr($periodo, 0, 4))
                        ->where('per_limes', substr($periodo, 4, 2))
                        ->pluck('desc_liqui', 'nro_liqui')
                        ->toArray();
                })
                ->searchable()
                ->name('nro_liqui')
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when(
                        $data['value'],
                        fn ($query) => $query->where('nro_liqui', $data['value'])
                    );
                })
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
