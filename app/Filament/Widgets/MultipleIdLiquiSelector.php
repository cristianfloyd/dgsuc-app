<?php

namespace App\Filament\Widgets;

use App\Models\Mapuche\Dh22;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

/**
 * @property mixed $form
 */
class MultipleIdLiquiSelector extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static ?int $sort = 2;
    protected static string $view = 'filament.widgets.id-liqui-selector';
    public ?array $selectedIdLiqui = null;
    protected array $casts = [
        'form' => 'array',
    ];

    public function mount(): void
    {
        $this->form->fill();
    }


    /**
     * @return Form
     */
    #[Computed]
    public function form(): Form
    {
        return $this->makeForm();
    }

    protected function makeForm(): Form
    {
        return Form::make($this)
            ->schema([
                Select::make('selectedIdLiqui')
                    ->label('Seleccionar Liquidaciones')
                    ->multiple()
                    ->options(Dh22::getLiquidacionesForWidget()->pluck('desc_liqui', 'nro_liqui'))
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->dispatch('idsLiquiSelected', $state);
                        Log::debug("idsLiquiSelected", ['state' => $state]);
                    }),
            ]);
    }

    #[On('updated-periodo-fiscal')]
    public function handlePeriodoFiscalSelected($periodoFiscal): void
    {
        // $periodoFiscal es un array con las claves 'year' y 'month'
        // Actualizar las opciones del select basadas en el periodo fiscal seleccionado
        $liquidaciones = Dh22::getLiquidacionesForWidget()
            ->when($periodoFiscal, function ($query) use ($periodoFiscal) {
                return $query->whereRaw("CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) = ?", [
                    $periodoFiscal['year'] . str_pad($periodoFiscal['month'], 2, '0', STR_PAD_LEFT)
                ]);
            })
            ->pluck('desc_liqui', 'nro_liqui');


        $this->form->getComponent('selectedIdLiqui')->options($liquidaciones);

        // Limpiar la selección actual
        $this->selectedIdLiqui = null;

        // Actualizar el formulario
        $this->form->fill();

        Log::debug("PeriodoFiscalSelected en MultipleIdLiquiSelector", ['periodoFiscal' => $periodoFiscal]);
    }
}
