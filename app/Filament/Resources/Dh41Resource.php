<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Dh41Resource\Pages;
use App\Models\Dh41;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class Dh41Resource extends Resource
{
    protected static ?string $model = Dh41::class;
    protected static ?string $label = 'Ganancias';
    protected static ?string $navigationLabel = 'Ganancias Iniciales';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Liquidaciones';

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
                //
                TextColumn::make('nro_legaj')->Label('Nro Legajo')->searchable()->sortable(),
                TextColumn::make('per_anoga'),
	            TextColumn::make('per_mesga'),
	            TextColumn::make('imp_bruto'),
	            TextColumn::make('imp_jubun'),
	            TextColumn::make('nro_liqui'),
            ])
            ->filters([
                //filtrar por numero de liqui
                SelectFilter::make('nro_liqui')
                ->options(
                    Dh41::query()
                    ->whereNotNull('nro_liqui')
                    ->select('nro_liqui')
                    ->distinct() //para que no se repitan los valores
                    ->pluck('nro_liqui', 'nro_liqui') //para que se muestren los valores en el select
                    ->toArray()
                    // que no se almacenen los nulos
                ),
                //filtrar por periodo
                SelectFilter::make('per_anoga') //nombre del filtro
                ->options(
                    Dh41::query()
                    ->whereNotNull('per_anoga')
                    ->select('per_anoga')
                    ->distinct() //para que no se repitan los valores
                    ->pluck('per_anoga', 'per_anoga') //para que se muestren los valores en el select
                    ->toArray()
                )
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
            'index' => Pages\ListDh41s::route('/'),
            'create' => Pages\CreateDh41::route('/create'),
            'edit' => Pages\EditDh41::route('/{record}/edit'),
        ];
    }
}
