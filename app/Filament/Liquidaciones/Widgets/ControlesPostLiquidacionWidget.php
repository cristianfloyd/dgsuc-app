<?php

namespace App\Filament\Liquidaciones\Widgets;

use App\Models\LiquidacionControl;
use Illuminate\Support\Facades\Log;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ControlesPostLiquidacionWidget extends BaseWidget
{
    /**
     * Define las estadísticas que se mostrarán en el widget
     * 
     * @return array Array de objetos Stat para mostrar en el widget
     */
    protected function getStats(): array
    {
        try {
            return [
                Stat::make('Controles Pendientes', $this->getControlesPendientes())
                    ->description('Controles que aún no se han ejecutado')
                    ->descriptionIcon('heroicon-o-clock')
                    ->color('warning'),
                    
                Stat::make('Controles con Error', $this->getControlesConError())
                    ->description('Controles que fallaron en su ejecución')
                    ->descriptionIcon('heroicon-o-exclamation-circle')
                    ->color('danger'),
                    
                Stat::make('Controles Completados', $this->getControlesCompletados())
                    ->description('Controles ejecutados correctamente')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),
            ];
        } catch (\Exception $e) {
            Log::error('Error al generar estadísticas de controles post-liquidación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Devolver estadísticas vacías en caso de error
            return [
                Stat::make('Controles Pendientes', 0)->color('warning'),
                Stat::make('Controles con Error', 0)->color('danger'),
                Stat::make('Controles Completados', 0)->color('success'),
            ];
        }
    }

    /**
     * Obtiene la cantidad de controles pendientes
     * 
     * @return int Número de controles pendientes
     */
    protected function getControlesPendientes(): int
    {
        try {
            return LiquidacionControl::where('estado', 'pendiente')->count();
        } catch (\Exception $e) {
            Log::error('Error al obtener controles pendientes', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Obtiene la cantidad de controles con error
     * 
     * @return int Número de controles con error
     */
    protected function getControlesConError(): int
    {
        try {
            return LiquidacionControl::where('estado', 'error')->count();
        } catch (\Exception $e) {
            Log::error('Error al obtener controles con error', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Obtiene la cantidad de controles completados
     * 
     * @return int Número de controles completados
     */
    protected function getControlesCompletados(): int
    {
        try {
            return LiquidacionControl::where('estado', 'completado')->count();
        } catch (\Exception $e) {
            Log::error('Error al obtener controles completados', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}