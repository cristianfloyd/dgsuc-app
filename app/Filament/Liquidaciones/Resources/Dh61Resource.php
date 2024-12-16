<?php

namespace App\Filament\Liquidaciones\Resources;

use Filament\Forms;
use App\Models\Dh61;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Liquidaciones\Resources\Dh61Resource\Pages;

class Dh61Resource extends Resource
{
    protected static ?string $model = Dh61::class;
    protected static ?string $modelLabel = 'Basicos Historico';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Personal';



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codc_categ')
                    ->searchable(),
                TextColumn::make('equivalencia')
                    ->searchable(),
                TextColumn::make('tipo_escal')
                    ->searchable(),
                TextColumn::make('nro_escal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('impp_basic')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codc_dedic')
                    ->searchable(),
                TextColumn::make('sino_mensu')
                    ->searchable(),
                TextColumn::make('sino_djpat')
                    ->searchable(),
                TextColumn::make('vig_caano')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vig_cames')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('desc_categ')
                    ->searchable(),
                TextColumn::make('sino_jefat')
                    ->searchable(),
                TextColumn::make('impp_asign')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('computaantig')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('controlcargos')
                    ->boolean(),
                TextColumn::make('controlhoras')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('controlpuntos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('controlpresup')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('horasmenanual')
                    ->searchable(),
                TextColumn::make('cantpuntos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estadolaboral')
                    ->searchable(),
                TextColumn::make('nivel')
                    ->searchable(),
                TextColumn::make('tipocargo')
                    ->searchable(),
                TextColumn::make('remunbonif')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('noremunbonif')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('remunnobonif')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('noremunnobonif')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('otrasrem')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dto1610')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reflaboral')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('refadm95')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('critico')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('jefatura')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gastosrepre')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codigoescalafon')
                    ->searchable(),
                TextColumn::make('noinformasipuver')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('noinformasirhu')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('imppnooblig')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('aportalao')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('factor_hs_catedra')
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
            'index' => Pages\ListDh61s::route('/'),
            'edit' => Pages\EditDh61::route('/{record}/edit'),
        ];
    }
    protected static function getTableRecordKey(): string
    {
        return 'id';
    }
}
