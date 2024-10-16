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
 * Representa un widget de Filament que permite seleccionar una liquidación de un modelo Dh22.
 * El widget muestra un campo de selección que se llena con las opciones de liquidaciones disponibles.
 * Cuando se selecciona una liquidación, se dispara un evento 'idLiquiSelected' con el valor seleccionado.
 * @property mixed $form
 */
class IdLiquiSelector extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static ?int $sort = 2;
    protected static string $view = 'filament.widgets.id-liqui-selector';
    protected static ?string $heading = 'Selector de Liquidación';
    public ?string $selectedIdLiqui = null;
    protected array $periodoFiscal = [];
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
            /**
             * Renders a Filament form component that allows the user to select a liquidation from a list of available liquidations.
             * The list of liquidations is filtered by the current fiscal period, if set.
             * When a liquidation is selected, an 'idLiquiSelected' event is dispatched with the selected liquidation ID.
             */
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
    public function filtrarPorPeriodoFiscal($periodoFiscal): void
    {

        // Asigna el valor de $periodoFiscal al atributo $this->periodoFiscal.
        // Si $periodoFiscal es null, se asigna un array vacío como valor predeterminado.
        // Esto permite filtrar las liquidaciones por período fiscal cuando se actualiza el widget.
        $this->periodoFiscal = $periodoFiscal ?? [];

        // limpiar el valor seleccionado en el campo de selección
        $this->selectedIdLiqui = null;

        // actualizar el formulario
        $this->form->fill();
    }
}
