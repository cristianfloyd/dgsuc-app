<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\AfipMapucheSicoss;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\AfipMapucheSicossResource\Pages;

class AfipMapucheSicossResource extends Resource
{
    protected static ?string $model = AfipMapucheSicoss::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('periodo_fiscal')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('cuil')
                    ->required()
                    ->disabled(),
            ]);
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record ? "{$record->periodo_fiscal} - {$record->cuil}" : '';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periodo_fiscal'),
                TextColumn::make('cuil'),
                TextColumn::make('dh01.nro_legaj')
                    ->label('Nro Legajo'),
                TextColumn::make('dh01.cuil_completo')
                    ->label('Cuil dh01'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                ->url(fn (AfipMapucheSicoss $record): string =>
                    static::getUrl('edit', ['record' => $record->unique_id])
                ),
            ])
            ->defaultSort('periodo_fiscal', 'asc')
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

    public static function getRecordRouteKeyName(): string
    {
        return 'unique_id';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->with('dh01')
    //         ->withAggregate('dh01', 'nro_legaj');
    // }
}
