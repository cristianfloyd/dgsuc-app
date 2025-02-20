<?php

namespace App\Filament\Afip\Pages\Components;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Colors\Color;

class SicossTotalesInfoList extends Infolist
{
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('total_aportes')
                    ->label('Total Aportes')
                    ->money('ARS')
                    ->color(Color::Blue)
                    ->size(TextEntry\TextEntrySize::Large),
                TextEntry::make('total_contribuciones')
                    ->label('Total Contribuciones')
                    ->money('ARS')
                    ->color(Color::Blue)
                    ->size(TextEntry\TextEntrySize::Large),
                TextEntry::make('total_remunerativo')
                    ->label('Total Remunerativo Imponible')
                    ->money('ARS')
                    ->color(Color::Emerald)
                    ->size(TextEntry\TextEntrySize::Large),
                TextEntry::make('total_no_remunerativo')
                    ->label('Total No Remunerativo Imponible')
                    ->money('ARS')
                    ->color(Color::Emerald)
                    ->size(TextEntry\TextEntrySize::Large),
            ])
            ->columns(4);
    }
}
