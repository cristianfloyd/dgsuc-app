<?php

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\AfipMapucheSicoss;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\FilamentTableInitializationTrait;
use App\Traits\FilamentAfipMapucheSicossTableTrait;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Afip\Resources\AfipMapucheSicossResource\Pages;
use App\Contracts\TableService\AfipMapucheSicossTableServiceInterface;
use App\Filament\Afip\Resources\AfipMapucheSicossResource\RelationManagers;

class AfipMapucheSicossResource extends Resource
{
    use FilamentAfipMapucheSicossTableTrait;
    protected static ?string $model = AfipMapucheSicoss::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'AFIP';




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periodo_fiscal')
                                    ->label('Período Fiscal')
                                    ->sortable()
                                    ->searchable(),
                                Tables\Columns\TextColumn::make('cuil')
                                    ->label('CUIL')
                                    ->sortable()
                                    ->searchable(),
                                Tables\Columns\TextColumn::make('apnom')
                                    ->label('Apellido y Nombre')
                                    ->sortable()
                                    ->searchable(),
                                Tables\Columns\IconColumn::make('conyuge')
                                    ->label('Cónyuge')
                                    ->boolean(),
                                Tables\Columns\TextColumn::make('cant_hijos')
                                    ->label('Cant. Hijos')
                                    ->numeric(),
                                Tables\Columns\TextColumn::make('cod_situacion')
                                    ->label('Situación'),
                                Tables\Columns\TextColumn::make('cod_cond')
                                    ->label('Condición'),
                                Tables\Columns\TextColumn::make('rem_total')
                                    ->label('Remuneración Total')
                                    ->money('ARS'),
                                Tables\Columns\TextColumn::make('rem_impo1')
                                    ->label('Remuneración 1')
                                    ->money('ARS'),
                                Tables\Columns\TextColumn::make('asig_fam_pag')
                                    ->label('Asig. Familiar')
                                    ->money('ARS')
                
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
            'index' => Pages\ListAfipMapucheSicosses::route('/'),
            'create' => Pages\CreateAfipMapucheSicoss::route('/create'),
            'import' => Pages\ImportAfipMapucheSicoss::route('/import'),
        ];
    }
}
