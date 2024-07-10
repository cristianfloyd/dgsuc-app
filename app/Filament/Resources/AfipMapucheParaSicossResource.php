<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AfipMapucheParaSicossResource\Pages;
use App\Filament\Resources\AfipMapucheParaSicossResource\RelationManagers;
use App\Models\AfipMapucheParaSicoss;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AfipMapucheParaSicossResource extends Resource
{
    protected static ?string $model = AfipMapucheParaSicoss::class;

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
                //
                TextColumn::make('d.nro_legaj')->label('Nro Legaj')->sortable()->searchable(),
                TextColumn::make('nro_liqui')->label('Nro Liqui'),
                TextColumn::make('desc_estado_liquidacion')->label('Estado Liquidación'),
                TextColumn::make('nro_cargo')->label('Nro Cargo'),
                TextColumn::make('cuil')->label('CUIL'),
                TextColumn::make('inicio_rel_lab')->label('Inicio Relación Laboral'),
                TextColumn::make('fin_rel_lab')->label('Fin Relación Laboral'),
                TextColumn::make('retribucion_pactada')->label('Retribución Pactada'),
                TextColumn::make('domicilio')->label('Domicilio'),
                TextColumn::make('actividad')->label('Actividad'),
                TextColumn::make('puesto')->label('Puesto'),
                TextColumn::make('ccct')->label('CCCT'),
                TextColumn::make('categoria')->label('Categoría'),
                TextColumn::make('covid')->label('COVID'),

            ])
            ->filters([
                //
            ])
            ->actions([
                //
                ])
            ->bulkActions([
                //
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
            'index' => Pages\ListAfipMapucheParaSicosses::route('/'),
            'create' => Pages\CreateAfipMapucheParaSicoss::route('/create'),
            'edit' => Pages\EditAfipMapucheParaSicoss::route('/{record}/edit'),
        ];
    }

}
