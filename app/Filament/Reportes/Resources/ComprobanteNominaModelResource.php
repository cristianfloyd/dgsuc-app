<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ComprobanteNominaModel;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Reportes\Resources\ComprobanteNominaModelResource\Pages;
use App\Filament\Reportes\Resources\ComprobanteNominaModelResource\RelationManagers;
use App\Filament\Resources\ComprobanteNominaModelResource\Pages\ImportComprobanteNomina;

class ComprobanteNominaModelResource extends Resource
{
    protected static ?string $model = ComprobanteNominaModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('anio_periodo')
                    ->label('Año')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('mes_periodo')
                    ->label('Mes')
                    ->formatStateUsing(fn ($state) => nombreMes($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('numero_liquidacion')
                    ->label('Liquidación')
                    ->searchable(),

                Tables\Columns\TextColumn::make('descripcion_liquidacion')
                    ->label('Descripción')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('importe_neto')
                    ->label('Importe Neto')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('area_administrativa')
                    ->label('Área')
                    ->searchable(),

                Tables\Columns\TextColumn::make('descripcion_retencion')
                    ->label('Retención')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('importe_retencion')
                    ->label('Importe Retención')
                    ->money('ARS')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('anio_periodo')
                    ->label('Año')
                    ->options(fn () => ComprobanteNominaModel::distinct()
                        ->pluck('anio_periodo', 'anio_periodo')
                        ->toArray()),

                SelectFilter::make('mes_periodo')
                    ->label('Mes')
                    ->options(fn () => collect(range(1, 12))->mapWithKeys(fn ($mes) =>
                        [$mes => nombreMes($mes)]
                    )->toArray()),

                SelectFilter::make('area_administrativa')
                    ->label('Área')
                    ->options(fn () => ComprobanteNominaModel::distinct()
                        ->pluck('area_administrativa', 'area_administrativa')
                        ->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListComprobanteNominaModels::route('/'),

        ];
    }
}
