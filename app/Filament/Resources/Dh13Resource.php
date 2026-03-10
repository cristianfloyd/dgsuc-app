<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Dh13Resource\Pages\CreateDh13;
use App\Filament\Resources\Dh13Resource\Pages\EditDh13;
use App\Filament\Resources\Dh13Resource\Pages\ListDh13s;
use App\Models\Dh13;
use App\Services\EncodingService;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

use function strlen;

class Dh13Resource extends Resource
{
    protected static ?string $model = Dh13::class;

    protected static string $relationship = 'dh13s';

    protected static ?string $modelLabel = 'Formulas';

    protected static string|UnitEnum|null $navigationGroup = 'Conceptos';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([

            ])
            ->defaultSort('codn_conce', 'asc')
            ->defaultSort('nro_orden_formula', 'asc');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDh13s::route('/'),
            'create' => CreateDh13::route('/create'),
            'edit' => EditDh13::route('/{record}/edit'),
        ];
    }
}
