<?php

namespace App\Filament\Liquidaciones\Resources\DH21hs\DH21hs;

use App\Filament\Liquidaciones\Resources\DH21hs\Pages\ListDH21hs;
use App\Models\Mapuche\DH21h;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DH21hResource extends Resource
{
    protected static ?string $model = DH21h::class;

    protected static ?string $modelLabel = 'Liquidaciones Historico';

    protected static ?string $navigationLabel = 'Liquidaciones Historico';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Liquidaciones';

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nro_legaj')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nro_cargo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('impp_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipo_conce')
                    ->searchable(),
                TextColumn::make('nov1_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nov2_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nro_orimp')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipoescalafon')
                    ->searchable(),
                TextColumn::make('nrogrupoesc')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codigoescalafon')
                    ->searchable(),
                TextColumn::make('codc_regio')
                    ->searchable(),
                TextColumn::make('codc_uacad')
                    ->searchable(),
                TextColumn::make('codn_area')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_subar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_fuent')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_progr')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_subpr')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_proye')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_activ')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_obra')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_final')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_funci')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ano_retro')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('mes_retro')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('detallenovedad')
                    ->searchable(),
                TextColumn::make('codn_grupo_presup')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipo_ejercicio')
                    ->searchable(),
                TextColumn::make('codn_subsubar')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([

            ])
            ->recordActions([

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ])
            ->deferLoading()
            ->defaultPaginationPageOption(5);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [

        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListDH21hs::route('/'),
        ];
    }
}
