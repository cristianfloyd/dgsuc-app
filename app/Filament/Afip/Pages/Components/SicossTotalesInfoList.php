<?php

namespace App\Filament\Afip\Pages\Components;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\TextSize;

class SicossTotalesInfoList extends Schema
{
    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('total_aportes')
                    ->label('Total Aportes')
                    ->money('ARS')
                    ->color(Color::Blue)
                    ->size(TextSize::Large),
                TextEntry::make('total_contribuciones')
                    ->label('Total Contribuciones')
                    ->money('ARS')
                    ->color(Color::Blue)
                    ->size(TextSize::Large),
                TextEntry::make('total_remunerativo')
                    ->label('Total Remunerativo Imponible')
                    ->money('ARS')
                    ->color(Color::Emerald)
                    ->size(TextSize::Large),
                TextEntry::make('total_no_remunerativo')
                    ->label('Total No Remunerativo Imponible')
                    ->money('ARS')
                    ->color(Color::Emerald)
                    ->size(TextSize::Large),
            ])
            ->columns(4);
    }
}
