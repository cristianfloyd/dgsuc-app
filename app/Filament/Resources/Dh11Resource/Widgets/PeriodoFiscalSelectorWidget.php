<?php

namespace App\Filament\Widgets;

use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use App\Services\Mapuche\PeriodoFiscalService;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;

/**
 * Widget que permite seleccionar el período fiscal actual.
 */
class PeriodoFiscalSelectorWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.fiscal-period-selector';
    protected $listeners = ['refreshFiscalPeriod' => '$refresh'];
    protected $periodoFiscalService;
    protected $periodoFiscal;

    public $year;
    public $month;



    public function boot(PeriodoFiscalService $periodoFiscalService): void
    {
        $this->periodoFiscalService = $periodoFiscalService;
    }
    public function mount()
    {
        $this->periodoFiscal = $this->periodoFiscalService->getPeriodoFiscalFromDatabase();
        $this->year = $this->periodoFiscal['year'];
        $this->month = $this->periodoFiscal['month'];
        $this->form->fill();
    }

    public function submit()
    {
        $this->periodoFiscalService->setPeriodoFiscal($this->year, $this->month);
        $this->dispatch('fiscalPeriodUpdated');
    }

    protected function form(Form $form): Form
    {
        return $form
            ->schema([
            Select::make('year')
                ->label('Año Fiscal')
                ->default($this->year)
                ->options($this->getYearOptions())
                ->required(),
            Select::make('month')
                ->label('Mes Fiscal')
                ->default($this->month)
                ->options($this->getMonthOptions())
                ->required(),
            Actions::make([
                    Action::make('set')->label('Establecer')
                        ->color('success')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->action('submit')
                        ->dispatch('updated-periodo-fiscal', [$this->periodoFiscal]),
                ])
                    ->alignCenter()
                    ->verticallyAlignEnd(),
            ])
            ->columns(3);
    }

    // protected function getFormSchema(): array
    // {
    //     return [
    //         Select::make('year aaaa')
    //             ->label('Año Fiscal')
    //             ->options($this->getYearOptions())
    //             ->required(),
    //         Select::make('month')
    //             ->label('Mes Fiscal')
    //             ->options($this->getMonthOptions())
    //             ->required(),
    //     ];
    // }


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

    public static function canView(): bool
    {
        return true;
    }
}
