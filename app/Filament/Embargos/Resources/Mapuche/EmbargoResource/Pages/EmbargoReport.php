<?php

namespace App\Filament\Embargos\Resources\Mapuche\EmbargoResource\Pages;

use App\Models\Mapuche\Dh22;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use App\Services\Reportes\EmbargoReportService;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Embargos\Resources\Mapuche\EmbargoResource;

class EmbargoReport extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = EmbargoResource::class;
    protected static string $view = 'filament.resources.embargo.pages.report';

    public $nro_liqui;
    public $reportData;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('nro_liqui')
                ->label('Liquidación')
                ->options(function() {
                    return Dh22::query()->select('nro_liqui')
                        ->distinct()
                        ->orderByDesc('nro_liqui')
                        ->pluck('nro_liqui', 'nro_liqui');
                })
                ->required()
        ];
    }

    public function generateReport(): void
    {
        $reportService = app(EmbargoReportService::class);
        $this->reportData = $reportService->generateReport($this->nro_liqui);
    }
}
