<?php

namespace App\Filament\Reportes\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Reportes\OrdenesDescuento;
use App\Filament\Reportes\Resources\OrdenesDescuentoResource\Pages;

class OrdenesDescuentoResource extends Resource
{
    protected static ?string $model = OrdenesDescuento::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'create' => Pages\CreateOrdenesDescuento::route('/create'),
            'edit' => Pages\EditOrdenesDescuento::route('/{record}/edit'),
        ];
    }
}
