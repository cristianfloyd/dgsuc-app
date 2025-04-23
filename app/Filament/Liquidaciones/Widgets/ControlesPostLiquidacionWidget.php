<?php

namespace App\Filament\Liquidaciones\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ControlesPostLiquidacionWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Controles Pendientes', $this->getControlesPendientes())
                ->color('warning'),
            Stat::make('Controles con Error', $this->getControlesConError())
                ->color('danger'),
            Stat::make('Controles Completados', $this->getControlesCompletados())
                ->color('success'),
        ];
    }
}