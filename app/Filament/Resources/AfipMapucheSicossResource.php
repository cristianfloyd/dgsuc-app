<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AfipMapucheSicossResource\Pages;
use App\Filament\Resources\AfipMapucheSicossResource\RelationManagers;
use App\Models\AfipMapucheSicoss;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AfipMapucheSicossResource extends Resource
{
    protected static ?string $model = AfipMapucheSicoss::class;
    protected static ?string $modelLabel = 'Afip Sicoss';
    protected static ?string $navigationGroup = 'Afip';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('periodo_fiscal')->label('Periodo Fiscal'),
                TextInput::make('cuil')->label('CUIL'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('periodo_fiscal')->label('Periodo Fiscal'),
                TextColumn::make('cuil')->label('CUIL'),
            ])
            ->defaultSort('periodo_fiscal', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn(AfipMapucheSicoss $record) => static::getUrl('edit', ['record' => $record->getKey()])
                ),
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
            'edit' => Pages\EditAfipMapucheSicoss::route('/{record}/edit'),
        ];
    }

    protected function getDefaultSortColumn(): ?string
    {
        return 'periodo_fiscal';
    }

    protected function getDefaultSortDirection(): ?string
    {
        return 'desc';
    }
}
