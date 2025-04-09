<?php

namespace App\Filament\Widgets;

use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Actions\Action;
use App\Services\Mapuche\PeriodoFiscalService;
use Filament\Forms\Concerns\InteractsWithForms;

/**
 * Widget que permite seleccionar el período fiscal actual.
 * @property mixed $form
 */
class PeriodoFiscalSelectorWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.fiscal-period-selector';

    protected PeriodoFiscalService $periodoFiscalService;
    protected array $periodoFiscal;

    public int $year;
    public int $month;



    public function boot(PeriodoFiscalService $periodoFiscalService): void
    {
        $this->periodoFiscalService = $periodoFiscalService;
    }
    /**
     * Inicializa el widget cargando el período fiscal actual
     */
    public function mount(): void
    {
        try {
            // Obtenemos el período fiscal actual (el servicio ya verifica la sesión primero)
            $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
            
            // Convertimos a enteros para los selectores
            $this->year = (int)$periodoFiscal['year'];
            $this->month = (int)$periodoFiscal['month'];
            
            $this->form->fill();
            
            Log::debug("Widget PeriodoFiscal montado con año: {$this->year}, mes: {$this->month}");
        } catch (\Exception $e) {
            Log::error("Error al montar el widget PeriodoFiscal: " . $e->getMessage());
            // Valores predeterminados en caso de error
            $this->year = Carbon::now()->year;
            $this->month = Carbon::now()->month;
        }
    }

    public function submit(): void
    {
        $this->periodoFiscalService->setPeriodoFiscal($this->year, $this->month);
        $this->periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
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
                        ->button()
                        ->badge("$this->year - $this->month")
                        ->icon('heroicon-o-cog-6-tooth')
                        ->action(function ($livewire) {
                            $this->submit();
                            $livewire->dispatch('updated-periodo-fiscal', $this->periodoFiscal);
                        }),
                ])
                    ->alignCenter()
                    ->verticallyAlignEnd(),
            ])
            ->columns(3);
    }

    private function getYearOptions(): array
    {
        $currentYear = Carbon::now()->year;
        return array_combine(keys: range($currentYear - 5, $currentYear + 1), values: range($currentYear - 5, $currentYear + 1));
    }

    private function getMonthOptions(): array
    {
        return [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
    }



    public static function canView(): bool
    {
        return true;
    }
}
