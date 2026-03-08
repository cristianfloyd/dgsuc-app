<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Dh41Resource\Pages\ListDh41s;
use App\Filament\Resources\Dh41Resource\Pages\CreateDh41;
use App\Filament\Resources\Dh41Resource\Pages\EditDh41;
use App\Filament\Resources\Dh41Resource\Pages;
use App\Models\Dh41;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Liquidaciones';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

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
                            ->toArray(),
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
                            ->toArray(),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDh41s::route('/'),
            'create' => CreateDh41::route('/create'),
            'edit' => EditDh41::route('/{record}/edit'),
        ];
    }
}
