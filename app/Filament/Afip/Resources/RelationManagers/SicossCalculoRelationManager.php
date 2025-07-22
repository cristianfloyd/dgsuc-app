<?php

namespace App\Filament\Afip\Resources\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SicossCalculoRelationManager extends RelationManager
{
    protected static string $relationship = 'sicossCalculo';

    protected static ?string $recordTitleAttribute = 'cuil';

    protected static ?string $title = 'Cálculo SICOSS';

    protected static ?string $modelLabel = 'Cálculo SICOSS';

    protected static ?string $pluralModelLabel = 'Cálculos SICOSS';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('remtotal')
                    ->label('Remuneración Total')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('rem1')
                    ->label('Remuneración 1')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('rem2')
                    ->label('Remuneración 2')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('aportesijp')
                    ->label('Aportes SIJP')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('aporteinssjp')
                    ->label('Aportes INSSJP')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('contribucionsijp')
                    ->label('Contribución SIJP')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('contribucioninssjp')
                    ->label('Contribución INSSJP')
                    ->money('ARS')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
