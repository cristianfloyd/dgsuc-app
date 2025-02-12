<?php

declare(strict_types=1);

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use App\Models\AfipMapucheSicossCalculo;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages\EditAfipMapucheSicossCalculo;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages\ListAfipMapucheSicossCalculos;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages\ImportAfipMapucheSicossCalculo;

class AfipMapucheSicossCalculoResource extends Resource
{
    protected static ?string $model = AfipMapucheSicossCalculo::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?string $navigationLabel = 'SICOSS Calculo';

    public static function getPages(): array
    {
        return [
            'index' => ListAfipMapucheSicossCalculos::route('/'),
            'edit' => EditAfipMapucheSicossCalculo::route('/{record}/edit'),
            'import' => ImportAfipMapucheSicossCalculo::route('/import'),
        ];
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cuil')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('remtotal')
                    ->money('ARS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('codc_uacad')
                    ->label('UA/CAD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('caracter')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('codc_uacad')
                    ->label('UA/CAD'),
                Tables\Filters\SelectFilter::make('caracter')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cuil')
                    ->required()
                    ->maxLength(11),
                Forms\Components\TextInput::make('remtotal')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('rem1')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('rem2')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('codc_uacad')
                    ->required()
                    ->maxLength(3),
                Forms\Components\TextInput::make('caracter')
                    ->required()
                    ->maxLength(4),
            ]);
    }
}
