<?php

namespace App\Filament\Reportes\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use App\Exports\OrdenesDescuentoExport;
use Filament\Tables\Columns\TextColumn;
use App\Exports\OrdenesDescuentoSheet200;
use App\Exports\OrdenesDescuentoSheet300;
use App\Models\Reportes\OrdenesDescuento;
use Illuminate\Database\Eloquent\Builder;
use App\Services\OrdenesDescuentoTableService;
use App\Exports\OrdenesDescuentoMultipleExport;
use App\Filament\Reportes\Resources\OrdenesDescuentoResource\Pages;

class OrdenesDescuentoResource extends Resource
{
    protected static ?string $model = OrdenesDescuento::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui'),
                TextColumn::make('desc_liqui')->sortable(),
                TextColumn::make('codc_uacad')->sortable()->searchable(),
                TextColumn::make('desc_item')->sortable()->searchable(),
                TextColumn::make('codn_funci')->sortable(),
                TextColumn::make('caracter')->sortable(),
                TextColumn::make('tipoescalafon')->sortable(),
                TextColumn::make('codn_fuent')->sortable(),
                TextColumn::make('nro_inciso')->sortable(),
                TextColumn::make('codn_progr')->sortable(),
                TextColumn::make('codn_conce')->sortable(),
                TextColumn::make('desc_conce')->sortable(),
                TextColumn::make('impp_conce')->sortable()
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('last_sync')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar Todo')
                    ->tooltip('Exportar todos los registros a un archivo Excel en dos hojas')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        return (new OrdenesDescuentoMultipleExport($livewire->getFilteredTableQuery()))
                            ->download('descuentos-y-aportes.xlsx');
                    }),
                Action::make('export200')
                    ->label('Ordenes Descuento')
                    ->tooltip('Exportar los conceptos de ordenes de descuento a un archivo Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        return (new OrdenesDescuentoSheet200($livewire->getFilteredTableQuery()->whereBetween('codn_conce', [200, 299])))
                            ->download('ordenes-descuento.xlsx');
                    }),
                Action::make('export300')
                    ->label('Aportes y Contrib.')
                    ->tooltip('Exportar Aportes y Contribuciones a un archivo Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        return (new OrdenesDescuentoSheet300($livewire->getFilteredTableQuery()->whereBetween('codn_conce', [300, 399])))
                            ->download('aportes-y-contribuciones.xlsx');
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ])
            ->deferLoading()
            ->persistFiltersInSession()
            ->defaultPaginationPageOption(5)
            ->reorderable(true)
            ->paginationPageOptions([5, 10, 25, 50, 100])
            ->emptyStateHeading('Seleccione un concepto')
            ->emptyStateDescription('Para visualizar los datos, primero debe seleccionar un concepto del filtro superior.')
            ->emptyStateIcon('heroicon-o-funnel');
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
        $tableService = new OrdenesDescuentoTableService();
        if (!$tableService->exists()) {
            $tableService->createAndPopulate();
        }

        return parent::getEloquentQuery();
    }
}
