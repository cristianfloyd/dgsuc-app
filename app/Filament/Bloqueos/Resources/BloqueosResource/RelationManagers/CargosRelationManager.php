<?php

namespace App\Filament\Bloqueos\Resources\BloqueosResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CargosRelationManager extends RelationManager
{
    protected static string $relationship = 'cargo';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nro_legaj,nro_cargo')
            ->columns([
                TextColumn::make('nro_legaj'),
                TextColumn::make('nro_cargo'),
                TextColumn::make('fec_alta')->date(),
                TextColumn::make('fec_baja')->date(),
            ])
            ->filters([

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
