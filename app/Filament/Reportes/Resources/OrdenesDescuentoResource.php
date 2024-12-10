<?php

namespace App\Filament\Reportes\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reportes\OrdenesDescuento;
use Illuminate\Database\Eloquent\Builder;
use App\Services\OrdenesDescuentoTableService;
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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
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
