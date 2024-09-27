<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Barryvdh\DomPDF\PDF;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Maatwebsite\Excel\Excel;
use App\Exports\ReportExport;
use App\Services\Dh12Service;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reportes\ConceptoListado;
use App\Services\ConceptoListadoService;
use Filament\Tables\Actions\SelectAction;
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codc_uacad')->label('dep'),
                TextColumn::make('periodo_fiscal')->label('Periodo'),
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
        return $service->getQueryForConcepto(request()->input('tableFilters.codn_conce', 225));
    }
}
