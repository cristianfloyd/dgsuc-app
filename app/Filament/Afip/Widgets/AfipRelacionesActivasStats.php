<?php

namespace App\Filament\Afip\Widgets;

use App\Models\AfipRelacionesActivas;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class AfipRelacionesActivasStats extends BaseWidget
{
    protected function getStats(): array
    {
        try {
            return [
                Stat::make(
                    'Total Relaciones Activas',
                    AfipRelacionesActivas::count()
                )
                    ->description('Total de registros')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->chart([7, 3, 4, 5, 6, 3, 5]),

                Stat::make(
                    'Altas del Mes',
                    AfipRelacionesActivas::where('codigo_movimiento', '00')
                        ->whereMonth('created_at', now()->month)
                        ->count()
                )
                    ->description('Nuevas relaciones laborales')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success'),

                Stat::make(
                    'Bajas del Mes',
                    AfipRelacionesActivas::where('codigo_movimiento', '01')
                        ->whereMonth('created_at', now()->month)
                        ->count()
                )
                    ->description('Relaciones finalizadas')
                    ->descriptionIcon('heroicon-m-arrow-trending-down')
                    ->color('danger'),
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al obtener estadísticas de relaciones activas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retornar estadísticas vacías en caso de error
            return [
                Stat::make('Total Relaciones Activas', 'Error de conexión')
                    ->description('No se pudo conectar a la base de datos')
                    ->color('danger'),

                Stat::make('Altas del Mes', 'Error de conexión')
                    ->description('No se pudo conectar a la base de datos')
                    ->color('danger'),

                Stat::make('Bajas del Mes', 'Error de conexión')
                    ->description('No se pudo conectar a la base de datos')
                    ->color('danger'),
            ];
        }
    }
}
