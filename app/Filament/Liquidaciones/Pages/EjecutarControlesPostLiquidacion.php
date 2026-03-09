<?php

namespace App\Filament\Liquidaciones\Pages;

use App\Services\LiquidacionControlService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;

class EjecutarControlesPostLiquidacion extends Page
{
    public $nroLiqui;

    public $resultados = [];

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $title = 'Ejecutar Controles Post-Liquidación';

    public function ejecutarControles(): void
    {
        $service = new LiquidacionControlService;

        foreach ($this->controles as $control) {
            $this->resultados[$control] = match ($control) {
                'cargos_liquidados' => $service->controlarCargosLiquidados($this->nroLiqui),
                'negativos' => $service->controlarNegativos($this->nroLiqui),
                // ... más controles
            };
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('nroLiqui')
                ->label('Número de Liquidación')
                ->required()
                ->options(function () {
                    // Obtener liquidaciones disponibles
                    return [/* ... */];
                }),
            CheckboxList::make('controles')
                ->label('Controles a Ejecutar')
                ->options([
                    'cargos_liquidados' => 'Control de Cargos Liquidados',
                    'negativos' => 'Control de Negativos',
                    'subrogancias' => 'Control de Subrogancias',
                    // ... más controles
                ]),
        ];
    }
}
