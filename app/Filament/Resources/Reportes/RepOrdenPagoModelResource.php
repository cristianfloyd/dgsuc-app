<?php

namespace App\Filament\Resources\Reportes;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reportes\RepOrdenPagoModel;
use App\Filament\Resources\Reportes\RepOrdenPagoModelResource\Pages;

class RepOrdenPagoModelResource extends Resource
{
    protected static ?string $model = RepOrdenPagoModel::class;
    protected static ?string $modelLabel = 'Orden de Pago';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('banco')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_funci')
                    ->searchable(),
                TextColumn::make('codn_fuent')
                    ->searchable(),
                TextColumn::make('codc_uacad')->label('U Acad')->searchable(),
                TextColumn::make('codn_progr')->searchable(),
                TextColumn::make('remunerativo')->numeric()->sortable(),
                TextColumn::make('no_remunerativo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('descuentos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('aportes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sueldo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estipendio')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('med_resid')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('productividad')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sal_fam')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('hs_extras')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Group::make('banco')
                    ->groupQueryUsing(fn($query) => $query->groupBy('banco')),
                Group::make('codn_funci')
                    ->groupQueryUsing(fn($query) => $query->groupBy('codn_funci')),
                Group::make('codn_fuent')
                    ->groupQueryUsing(fn($query) => $query->groupBy('codn_fuent')),
                Group::make('codc_uacad')
                    ->groupQueryUsing(fn($query) => $query->groupBy('codc_uacad')),
            ])
            ->defaultGroup('banco')
            ->groupRecordsTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Agrupar registros')
            )
            ->groupedBulkActions([
                Tables\Actions\ExportBulkAction::make('exportar'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'reporte' => Pages\ReporteOrdenPago::route('/reporte'),
            //            'create' => Pages\CreateRepOrdenPagoModel::route('/create'),
            //            'edit' => Pages\EditRepOrdenPagoModel::route('/{record}/edit'),
        ];
    }
}
