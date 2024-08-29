<?php

namespace App\Filament\Widgets;

use App\Services\Mapuche\PeriodoFiscalService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;

/**
 * Widget que permite seleccionar el período fiscal actual.
 */
class PeriodoFiscalSelectorWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.fiscal-period-selector';
    protected $listeners = ['refreshFiscalPeriod' => '$refresh'];


    public $year;
    public $month;

    protected function getFormSchema(): array
    {
        return [
            Select::make('year')
                ->label('Año Fiscal')
                ->options($this->getYearOptions())
                ->required(),
            Select::make('month')
                ->label('Mes Fiscal')
                ->options($this->getMonthOptions())
                ->required(),
        ];
    }

    private function getYearOptions(): array
    {
        $currentYear = date('Y');
        return array_combine(range($currentYear - 5, $currentYear + 1), range($currentYear - 5, $currentYear + 1));
    }

    private function getMonthOptions(): array
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
    }

    public function mount(PeriodoFiscalService $periodoFiscalService)
    {
        $periodoFiscal = $periodoFiscalService->getPeriodoFiscal();
        $this->year = $periodoFiscal['year'];
        $this->month = $periodoFiscal['month'];
    }

    public function submit(PeriodoFiscalService $periodoFiscalService)
    {
        $periodoFiscalService->setPeriodoFiscal($this->year, $this->month);
        $this->dispatch('fiscalPeriodUpdated');
    }

    // public function render()
    // {
    //     return view('filament.widgets.fiscal-period-selector');
    // }
}
