<?php

namespace App\Filament\Liquidaciones\Resources;

use App\Filament\Liquidaciones\Resources\DH21hResource\Pages\ListDH21hs;
use App\Models\Mapuche\DH21h;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DH21hResource extends Resource
{
    protected static ?string $model = DH21h::class;

    protected static ?string $modelLabel = 'Liquidaciones Historico';

    protected static ?string $navigationLabel = 'Liquidaciones Historico';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Liquidaciones';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nro_liqui')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nro_legaj')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nro_cargo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_conce')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impp_conce')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_conce')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nov1_conce')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nov2_conce')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nro_orimp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipoescalafon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nrogrupoesc')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigoescalafon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codc_regio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codc_uacad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codn_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_subar')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_fuent')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_progr')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_subpr')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_proye')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_activ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_obra')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_final')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_funci')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ano_retro')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mes_retro')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detallenovedad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codn_grupo_presup')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_ejercicio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codn_subsubar')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ])
            ->deferLoading()
            ->defaultPaginationPageOption(5);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDH21hs::route('/'),
        ];
    }
}
