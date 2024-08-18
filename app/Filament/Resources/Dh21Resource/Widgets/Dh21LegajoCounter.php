<?php

namespace App\Filament\Resources\Dh21Resource\Widgets;

use App\Models\Dh21;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class Dh21LegajoCounter extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Legajos', Dh21::distinctLegajos())
                ->description('Número total de legajos únicos')
                ->descriptionIcon('heroicon-o-user-group'),
        ];

    }
}
