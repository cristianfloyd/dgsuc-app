<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Barryvdh\DomPDF\PDF;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use Maatwebsite\Excel\Excel;
use App\Exports\ReportExport;
use App\Services\Dh12Service;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use App\Services\Mapuche\Dh22Service;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reportes\ConceptoListado;
use App\Services\ConceptoListadoService;
use Filament\Tables\Actions\SelectAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\EditReporteConceptoListado;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\ListReporteConceptoListados;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\CreateReporteConceptoListado;
use Carbon\Carbon;

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codc_uacad')->label('dep'),
                // TextColumn::make('periodo_fiscal')->label('Periodo'),
                TextColumn::make('periodo_fiscal')
                    ->label('Periodo')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->periodo_fiscal)
                    ->searchable(),
                TextColumn::make('nro_liqui')->label('Nro. Liq.')
                    ->sortable(),
                TextColumn::make('desc_liqui')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('nro_legaj'),
                TextColumn::make('cuil')->label('CUIL')->searchable(),
                TextColumn::make('desc_appat')->label('Apellido'),
                TextColumn::make('desc_nombr')->label('Nombre'),
                TextColumn::make('coddependesemp')->label('Oficina de Pago'),
                TextColumn::make('codigoescalafon'),
                TextColumn::make('nro_cargo')->label('Secuencia'),
                TextColumn::make('cargo')->label('Cargo')
                    ,
                TextColumn::make('codn_conce')->label('Concepto'),
                TextColumn::make('tipo_conce')->label('Tipo'),
                TextColumn::make('impp_conce')->label('Importe'),
            ])
            ->filters([
                SelectFilter::make('codn_conce')
                    ->label('Concepto')
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
                        // Log::info('$data en el select', [$data['value']]);
                        // return $query;
                    }),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
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
        $concepto = request()->input('tableFilters.codn_conce', 225);
        $query = $service->getQueryForConcepto($concepto);

        return $query;
    }
}
