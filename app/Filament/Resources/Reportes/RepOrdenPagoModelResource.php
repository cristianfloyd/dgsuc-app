<?php

namespace App\Filament\Resources\Reportes;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Reportes\RepOrdenPagoModel;
use App\Filament\Resources\Reportes\RepOrdenPagoModelResource\Pages;

class RepOrdenPagoModelResource extends Resource
{
    protected static ?string $model = RepOrdenPagoModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nro_liqui')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('banco')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_funci')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codn_fuent')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dh30.desc_abrev')->label('uAcad')->searchable(),
                Tables\Columns\TextColumn::make('codn_progr')->searchable(),
                Tables\Columns\TextColumn::make('remunerativo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_remunerativo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descuentos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('aportes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sueldo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estipendio')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('med_resid')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('productividad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sal_fam')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hs_extras')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListRepOrdenPagoModels::route('/'),
            //            'create' => Pages\CreateRepOrdenPagoModel::route('/create'),
            //            'edit' => Pages\EditRepOrdenPagoModel::route('/{record}/edit'),
        ];
    }
}
