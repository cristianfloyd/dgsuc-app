<?php

namespace App\Filament\Reportes\Resources\OrdenDePagoResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

    // protected static string $view = 'filament.reportes.resources.orden-de-pago-resource.widgets.orden-pago-stats-widget';
class OrdenPagoStatsWidget extends BaseWidget
{
    use MapucheConnectionTrait;
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        if (!$this->tieneTablaYDatos()) {
            return [];
        }

        $totales = $this->calcularTotales();

        return [
            Stat::make('Total Bruto', money($totales->total_bruto))
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            // Stat::make('Total Sueldo', money($totales->total_sueldo))
            //     ->icon('heroicon-o-banknotes')
            //     ->color('primary'),

            Stat::make('Total Neto', money($totales->total_neto))
                ->icon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('Total Aportes', money($totales->total_aportes))
                ->icon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make('Total Descuentos', money($totales->total_descuentos))
                ->icon('heroicon-o-arrow-trending-down')
                ->color('warning'),

            Stat::make('Total Imp. Gasto', money($totales->total_imp_gasto))
                ->icon('heroicon-o-receipt-percent')
                ->color('danger'),

            Stat::make('Cantidad Registros', number_format($totales->cantidad_registros))
                ->icon('heroicon-o-document-text')
                ->color('gray'),
        ];
    }

    private function tieneTablaYDatos(): bool
    {
        return Schema::connection($this->getConnectionName())->hasTable('suc.rep_orden_pago')
            && DB::connection($this->getConnectionName())->table('suc.rep_orden_pago')->count() > 0;
    }

    private function calcularTotales(): object
    {
        return DB::connection($this->getConnectionName())
            ->table('suc.rep_orden_pago')
            ->selectRaw('
                COUNT(*) as cantidad_registros,
                SUM(bruto) as total_bruto,
                SUM(sueldo) as total_sueldo,
                SUM(neto) as total_neto,
                SUM(aportes) as total_aportes,
                SUM(descuentos) as total_descuentos,
                SUM(imp_gasto) as total_imp_gasto
            ')
            ->first();
    }
}
