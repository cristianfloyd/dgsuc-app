<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Dh03;
use App\Models\Dh11;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Usuarios', User::count())
                ->description('Incremento del 20%')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Uso del Sistema', '95%')
                ->description('Rendimiento óptimo')
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color('warning'),


            // Estadísticas de Cargos
            Stat::make('Total Cargos', Dh03::count())
                ->description('Total de cargos en el sistema')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart($this->getCargosTrend())
                ->color('success'),

            // Estadísticas de Cargos Activos
            Stat::make('Cargos Activos', $this->getActiveCargos())
                ->description($this->getActivePercentage() . '% del total')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart($this->getActivosTrend())
                ->color('info'),

            // Estadísticas de Categorías
            Stat::make('Categorías', Dh11::count())
                ->description('Distribución por categoría')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart($this->getCategoriasTrend())
                ->color('warning'),
        ];
    }

    private function getActiveCargos(): int
    {
        return Dh03::where('chkstopliq', false)
            ->whereNull('fec_baja')
            ->count();
    }

    private function getActivePercentage(): float
    {
        $total = Dh03::count();
        $activos = $this->getActiveCargos();
        return $total > 0 ? round(($activos / $total) * 100, 2) : 0;
    }

    private function getCargosTrend(): array
    {
        return Dh03::select(DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(fec_alta)'))
            ->orderBy('fec_alta', 'DESC')
            ->limit(7)
            ->pluck('count')
            ->toArray();
    }

    private function getActivosTrend(): array
    {
        return Dh03::where('chkstopliq', false)
            ->whereNull('fec_baja')
            ->select(DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(fec_alta)'))
            ->orderBy('fec_alta', 'DESC')
            ->limit(7)
            ->pluck('count')
            ->toArray();
    }

    private function getCategoriasTrend(): array
    {
        return Dh11::withCount('dh03')
            ->orderBy('dh03_count', 'DESC')
            ->limit(7)
            ->pluck('dh03_count')
            ->toArray();
    }
}
