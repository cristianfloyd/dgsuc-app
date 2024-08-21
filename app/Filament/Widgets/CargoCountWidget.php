<?php

namespace App\Filament\Widgets;

use App\Models\Dh03;
use App\Models\Dh11;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class CargoCountWidget extends BaseWidget
{
    use InteractsWithPageFilters;


    protected function getStats(): array
    {
        $codigoescalafon = $this->filters['codigoescalafon'] ?? 'TODOS';

        switch ($codigoescalafon) {
            case 'TODOS':
                $cargoCount = Dh03::count();
                break;
            case 'DOCS':
                $cargoCount = Dh11::getCargosSecundario();
                break;
            case 'DOCU':
                $cargoCount = Dh11::getCargosUniversitario();
                break;
            default:
                $codc_categs = session('selected_codc_categs', []);
                $cargoCount = Dh03::whereIn('codc_categ', $codc_categs)->count();
                break;
        }




        return [
            Stat::make('Total de Cargos', $cargoCount)
                ->description('Basado en el escalafon seleccionado')
                ->descriptionIcon('heroicon-m-academic-cap'),
        ];
    }
}
