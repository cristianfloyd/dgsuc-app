<?php

namespace App\Filament\Widgets;

use Filament\Forms\Form;
use App\Models\Mapuche\Dh22;
use Filament\Widgets\Widget;
use Filament\Facades\Filament;
use Livewire\Attributes\Computed;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class IdLiquiSelector extends Widget implements HasForms
{
    use InteractsWithForms;

    protected $casts = [
        'form' => 'array',
    ];

    protected static string $view = 'filament.widgets.id-liqui-selector';

    public ?string $selectedIdLiqui = null;




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
                    ->label('Seleccionar Liquidacion')
                    ->options(Dh22::getLiquidacionesForWidget()->pluck('desc_liqui', 'nro_liqui'))
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->dispatch('idLiquiSelected', $state);
                    }),
            ]);
    }

}
