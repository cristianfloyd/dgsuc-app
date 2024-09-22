<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Barryvdh\DomPDF\PDF;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Maatwebsite\Excel\Excel;
use App\Exports\ReportExport;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reportes\ConceptoListado;
use App\Services\ConceptoListadoService;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\EditReporteConceptoListado;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\ListReporteConceptoListados;
use App\Filament\Resources\ReporteConceptoListadoResource\Pages\CreateReporteConceptoListado;
use Illuminate\Container\Attributes\Log;

class ReporteConceptoListadoResource extends Resource
{
    protected static ?string $model = ConceptoListado::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                TextColumn::make('periodo_fiscal')->label('Periodo')
                    ,
                TextColumn::make('desc_liqui'),
                TextColumn::make('nro_legaj'),
                TextColumn::make('cuil')->label('CUIL')
                    ,
                TextColumn::make('desc_appat')->label('Apellido'),
                TextColumn::make('desc_nombr')->label('Nombre'),
                TextColumn::make('coddependesemp')->label('Oficina de Pago'),
                TextColumn::make('codigoescalafon'),
                TextColumn::make('desc_categ')->label('Secuencia')
                    ->formatStateUsing(fn() => 'secuencia'),
                TextColumn::make('cargo')->label('Cargo')
                    ,
                TextColumn::make('codn_conce')->label('Concepto'),
                TextColumn::make('tipo_conce')->label('Tipo'),
                TextColumn::make('impp_conce')->label('Importe'),
            ])
            ->filters([
                SelectFilter::make('codn_conce')
                    ->options([
                        225 => '225',
                        258 => '258',
                        266 => '266',
                    ])
                    ->label('Concepto')
                    ->default(225),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('csv')->label('CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->button()
                ->action(function ($data)  {
                    //implementar un servicio que pase la query a excel a traves de laravel-excel
                    //https://github.com/Maatwebsite/Laravel-Excel
                        $query = static::getEloquentQuery();

                        // return Excel::download(new ReportExport($query), 'reporte_concepto_listado.xlsx');

                        return (new ReportExport($query))->download('invoices.csv', Excel::CSV, ['Content-Type' => 'text/csv']);

                    })
                    ->requiresConfirmation()
                    ->modalHeading('¿Desea descargar el reporte?')
                    ->modalDescription('Se generará un archivo Excel con los datos filtrados.')
                    ->modalSubmitActionLabel('Descargar'),
                Action::make('excel')->label('EXCEL')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Builder $query) {
                        //implementar una accrion que pase los datos de la tabla a un pdf a ttraves de laravel-exc
                        //https://github.com/Maatwebsite/Laravel-Excel
                        $query = static::getEloquentQuery();
                        return (new ReportExport($query))->download('invoices.xlsx', Excel::XLSX);
                            })
                            ->requiresConfirmation()
                            ->modalHeading('¿Desea descargar el reporte en PDF?')
                            ->modalDescription('Se generará un archivo PDF con los datos filtrados.')
                            ->modalSubmitActionLabel('Descargar')
            ])
            ->groups([
                Group::make('codc_uacad')->label('Dep')->collapsible(),
                'coddependesemp',
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
