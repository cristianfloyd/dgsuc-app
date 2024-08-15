<?php

namespace App\Filament\Resources\Dh21Resource\Widgets;

use App\Models\Dh21;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class Dh21Concepto101Total extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Concepto 101', number_format(Dh21::totalConcepto101(), 2, ',', '.'))
                ->description('Suma total del concepto 101')
                ->descriptionIcon('heroicon-o-currency-dollar'),
        ];
    }
}
