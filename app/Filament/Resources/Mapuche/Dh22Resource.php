<?php

namespace App\Filament\Resources\Mapuche;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Mapuche\Dh22Resource\Pages;
use App\Filament\Resources\Mapuche\Dh22Resource\RelationManagers;

class Dh22Resource extends Resource
{
    protected static ?string $model = Dh22::class;

    protected static ?string $modelLabel = 'Def. Liquidaciones';
    protected static ?string $navigationLabel = 'Def. Liquidaciones';


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
                Tables\Columns\TextColumn::make('nro_liqui')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('per_liano')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('per_limes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc_liqui')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fec_ultap')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('per_anoap')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('per_mesap')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc_lugap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fec_emisi')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc_emisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vig_emano')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vig_emmes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vig_caano')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vig_cames')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vig_coano')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vig_comes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_econo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sino_cerra')
                    ->searchable(),
                Tables\Columns\IconColumn::make('sino_aguin')
                    ->boolean(),
                Tables\Columns\IconColumn::make('sino_reten')
                    ->boolean(),
                Tables\Columns\IconColumn::make('sino_genimp')
                    ->boolean(),
                Tables\Columns\TextColumn::make('nrovalorpago')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('finimpresrecibos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_tipo_liqui')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListDh22s::route('/'),
            // 'create' => Pages\CreateDh22::route('/create'),
            // 'edit' => Pages\EditDh22::route('/{record}/edit'),
        ];
    }
}
