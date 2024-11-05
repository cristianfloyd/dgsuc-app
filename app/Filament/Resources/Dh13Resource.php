<?php

namespace App\Filament\Resources;

use App\Models\Dh12;
use App\Models\Dh13;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Dh13Resource\Pages;
use Illuminate\Contracts\Database\Eloquent\Builder;

class Dh13Resource extends Resource
{
    protected static ?string $model = Dh13::class;
    protected static string $relationship = 'dh13s';
    protected static ?string $modelLabel = 'Formulas';
    protected static ?string $navigationGroup = 'Conceptos';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $activeNavigationIcon = 'heroicon-o-document-text';
    protected static ?string $recordTitleAttribute = 'id';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles del Concepto')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('codn_conce')
                                    ->label('Código de Concepto')
                                    ->required(),
                                TextInput::make('nro_orden_formula')
                                    ->label('Número de Orden Fórmula')
                                    ->required(),
                                TextInput::make('desc_calcu')
                                    ->label('Descripción de Cálculo')
                                    ->required(),
                            ]),
                    ]),
                Section::make('Descripción de la Condición')
                    ->schema([
                        Textarea::make('desc_condi')
                            ->label('Descripción de la Condición')
                            ->rows(5)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codn_conce')->sortable()->searchable(),
                TextColumn::make('dh12.desc_conce')
                    ->label('Concepto')
                    ->formatStateUsing(fn($state) => EncodingService::toUtf8($state))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('nro_orden_formula')->label('Orden')->sortable(),
                TextColumn::make('desc_calcu')
                    ->label('Cálculo')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = mb_convert_encoding($column->getState(), 'ISO-8859-1', 'UTF-8');
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('desc_condi')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('codn_conce', 'asc')
            ->defaultSort('nro_orden_formula', 'asc');
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
            'index' => Pages\ListDh13s::route('/'),
            'create' => Pages\CreateDh13::route('/create'),
            'edit' => Pages\EditDh13::route('/{record}/edit'),
        ];
    }
}
