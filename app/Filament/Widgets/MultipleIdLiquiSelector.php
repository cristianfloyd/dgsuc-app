<?php

namespace App\Filament\Widgets;

use App\Models\Mapuche\Dh22;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

use const STR_PAD_LEFT;

/**
 * @property mixed $form
 */
class MultipleIdLiquiSelector extends Widget implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $selectedIdLiqui = null;

    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.id-liqui-selector';

    protected array $casts = [
        'form' => 'array',
    ];

    public function mount(): void
    {
        $this->form->fill();
    }

    #[Computed]
    public function form(): Schema
    {
        return $this->makeForm();
    }

    #[On('updated-periodo-fiscal')]
    public function handlePeriodoFiscalSelected($periodoFiscal): void
    {
        // $periodoFiscal es un array con las claves 'year' y 'month'
        // Actualizar las opciones del select basadas en el periodo fiscal seleccionado
        $liquidaciones = Dh22::getLiquidacionesForWidget()
            ->when($periodoFiscal, fn($query) => $query->whereRaw("CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) = ?", [
                $periodoFiscal['year'] . str_pad((string) $periodoFiscal['month'], 2, '0', STR_PAD_LEFT),
            ]))
            ->pluck('desc_liqui', 'nro_liqui');


        $this->form->getComponent('selectedIdLiqui')->options($liquidaciones);

        // Limpiar la selección actual
        $this->selectedIdLiqui = null;

        // Actualizar el formulario
        $this->form->fill();

        Log::debug('PeriodoFiscalSelected en MultipleIdLiquiSelector', ['periodoFiscal' => $periodoFiscal]);
    }

    protected function makeForm(): Schema
    {
        return Schema::make($this)
            ->components([
                Select::make('selectedIdLiqui')
                    ->label('Seleccionar Liquidaciones')
                    ->multiple()
                    ->options(Dh22::getLiquidacionesForWidget()->pluck('desc_liqui', 'nro_liqui'))
                    ->reactive()
                    ->afterStateUpdated(function ($state): void {
                        $this->dispatch('idsLiquiSelected', $state);
                        Log::debug('idsLiquiSelected', ['state' => $state]);
                    }),
            ]);
    }
}
