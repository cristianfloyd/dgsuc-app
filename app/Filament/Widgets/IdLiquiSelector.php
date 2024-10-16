<?php

namespace App\Filament\Widgets;

use Filament\Forms\Form;
use Livewire\Attributes\On;
use App\Models\Mapuche\Dh22;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

/**
 * Representa un widget de Filament que permite seleccionar una liquidación de un modelo Dh22.
 * El widget muestra un campo de selección que se llena con las opciones de liquidaciones disponibles.
 * Cuando se selecciona una liquidación, se dispara un evento 'idLiquiSelected' con el valor seleccionado.
 */
class IdLiquiSelector extends Widget implements HasForms
{
    use InteractsWithForms;
    protected static ?int $sort = 2;
    protected static string $view = 'filament.widgets.id-liqui-selector';
    protected static ?string $heading = 'Selector de Liquidación';

    public ?string $selectedIdLiqui = null;
    protected array $casts = [
        'form' => 'array',
    ];

    public function mount(): void
    {
        $this->form->fill();
    }

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
                    ->options(
                        Dh22::getLiquidacionesForWidget()
                            ->when($this->periodoFiscal, function ($query) {
                                return $query->whereRaw("CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) = ?", [
                                    $this->periodoFiscal['year'] . str_pad($this->periodoFiscal['month'], 2, '0', STR_PAD_LEFT)
                                ]);
                            })
                            ->pluck('desc_liqui', 'nro_liqui')
                    )
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->dispatch('idLiquiSelected', $state);
                        Log::debug("idLiquiSelected", ['state' => $state]);
                    }),
            ]);
    }

    #[On('updated-periodo-fiscal')]
    public function filtrarPorPeriodoFiscal($periodoFiscal)
    {
        $this->periodoFiscal = $periodoFiscal;
        $this->form->fill();
    }
}
