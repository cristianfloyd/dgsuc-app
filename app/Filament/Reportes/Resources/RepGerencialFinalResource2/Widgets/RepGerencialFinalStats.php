<?php

namespace App\Filament\Reportes\Resources\RepGerencialFinalResource\Widgets;

use App\Traits\MapucheConnectionTrait;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepGerencialFinalStats extends BaseWidget
{
    use MapucheConnectionTrait;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $connection = (new static())->getConnectionName();

        return Schema::connection($connection)->hasTable('suc.rep_ger_final')
            && DB::connection($connection)
                ->table('suc.rep_ger_final')
                ->exists();
    }

    protected function getStats(): array
    {
        $connection = $this->getConnectionName();
        $liquidaciones = session('idsLiquiSelected', []);

        if (empty($liquidaciones)) {
            return $this->getEmptyStats();
        }

        $totales = DB::connection($connection)
            ->table('suc.rep_ger_final')
            ->whereIn('nro_liqui', $liquidaciones)
            ->selectRaw('
                COUNT(DISTINCT nro_liqui) as total_liquidaciones,
                COUNT(DISTINCT nro_legaj) as total_agentes,
                SUM(imp_bruto) as total_bruto,
                SUM(imp_neto) as total_neto,
                SUM(imp_dctos) as total_descuentos,
                SUM(imp_aport) as total_aportes
            ')
            ->first();

        return [
            Stat::make('Total Liquidaciones', number_format($totales->total_liquidaciones))
                ->description('Cantidad de liquidaciones procesadas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Total Agentes', number_format($totales->total_agentes))
                ->description('Cantidad de agentes en la liquidación')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Total Bruto', '$ ' . number_format($totales->total_bruto, 2, ',', '.'))
                ->description('Importe bruto total')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Neto', '$ ' . number_format($totales->total_neto, 2, ',', '.'))
                ->description('Importe neto total')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Descuentos', '$ ' . number_format($totales->total_descuentos, 2, ',', '.'))
                ->description('Total de descuentos')
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color('danger'),

            Stat::make('Total Aportes', '$ ' . number_format($totales->total_aportes, 2, ',', '.'))
                ->description('Total de aportes patronales')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('warning'),
        ];
    }

    private function getEmptyStats(): array
    {
        return [
            Stat::make('Total Agentes', '0')
                ->description('Seleccione liquidaciones para ver estadísticas')
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('gray'),
        ];
    }
}
