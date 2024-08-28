<?php

namespace App\Filament\Resources\Dh11Resource\Widgets;


use App\Models\Dh11;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;

class ActualizarImppBasicWidget extends Widget implements HasForms
{
    use InteractsWithForms;
    protected static string $view = 'filament.resources.dh11-resource.widgets.actualizar-impp-basic-widget';
    public ?float $porcentaje = null;
    public Collection $previewData;
    public bool $showConfirmButton = false;


    public function mount(): void
    {
        $this->form->fill();
        $this->previewData = collect();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('porcentaje')
                    ->label('Porcentaje')
                    ->numeric()
                    ->required()
                    ->maxLength(10)
                    ->reactive()
                    ->placeholder('Ingrese el porcentaje de incremento')
                    ->suffixActions([
                        Action::make('previsualizar')
                            ->label('Previsualizar')
                            ->icon('heroicon-m-arrow-left-circle')
                            ->requiresConfirmation()
                            ->action('previsualizar'),
                    ]),
            ]);
    }

    public function previsualizar(): void
    {
        $this->validate();

        $factor = 1 + $this->porcentaje / 100;

        $this->previewData = Dh11::select('codc_categ', 'impp_basic')
            ->where('impp_basic', '>', 0)
            ->orderBy('codc_categ')
            ->get()
            ->map(function ($item) use ($factor) {
                $newImppBasic = $this->round_up($item->impp_basic * $factor, 3);
                return [
                    'codc_categ' => $item->codc_categ,
                    'impp_basic_actual' => $item->impp_basic,
                    'impp_basic_nuevo' => $newImppBasic,
                ];
            });
            $this->showConfirmButton = true;
    }

    public function confirmarCambios(): void
    {
        $factor = round(1 + $this->porcentaje / 100, 4);

        Dh11::chunk(100, function ($items) use ($factor) {
            foreach ($items as $item) {
                $item->actualizarImppBasicPorPorcentaje($this->porcentaje);
            }
        });

        Notification::make()
            ->title('Cambios confirmados')
            ->success()
            ->send();

        $this->previewData = collect();
        $this->showConfirmButton = false;
        $this->porcentaje = null;
    }

    /**
     * Redondea un número hacia arriba con un número específico de decimales.
     *
     * @param float $number El número a redondear.
     * @param int $precision El número de decimales a mantener.
     * @return float El número redondeado hacia arriba.
     */
    function round_up($number, $precision = 2)
    {
        $fig = (int) str_pad('1', $precision, '0');
        return ceil($number * $fig) / $fig;
    }

    public static function canView():bool
    {
        // return auth()->user()->can('manage_dh11');
        return true;
    }
}
