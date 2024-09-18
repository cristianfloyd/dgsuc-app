<?php

namespace App\Filament\Widgets;

use Filament\Forms\Form;
use App\Models\Mapuche\Dh22;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class MultipleIdLiquiSelector extends Widget implements HasForms
{
    use InteractsWithForms;
    protected static ?int $sort = 2;

    protected $casts = [
        'form' => 'array',
    ];

    protected static string $view = 'filament.widgets.id-liqui-selector';

    public ?array $selectedIdLiqui = null;




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
                    ->multiple()
                    ->options(Dh22::getLiquidacionesForWidget()->pluck('desc_liqui', 'nro_liqui'))
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->dispatch('idsLiquiSelected', $state);
                        Log::debug("idsLiquiSelected", ['state' => $state]);
                    }),
            ]);
    }

}
