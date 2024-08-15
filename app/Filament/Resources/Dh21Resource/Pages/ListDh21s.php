<?php

namespace App\Filament\Resources\Dh21Resource\Pages;

use Filament\Actions\Action;
use App\Filament\Resources\Dh21Resource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\LiquidacionesWidget;
use App\Filament\Resources\Dh21Resource\Widgets\Dh21LegajoCounter;
use App\Filament\Resources\Dh21Resource\Widgets\Dh21Concepto101Total;

class ListDh21s extends ListRecords
{
    protected static string $resource = Dh21Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Action::make('conceptos-totales')
            ->label('Ver Conceptos Totales')
            ->url(static::getResource()::getUrl('conceptos-totales')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Dh21LegajoCounter::class,
            Dh21Concepto101Total::class,
            LiquidacionesWidget::class,
        ];
    }
}
