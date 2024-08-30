<?php

namespace App\Filament\Resources\Dh11Resource\Widgets;

use App\Contracts\CategoryUpdateServiceInterface;
use App\Models\Dh11;
use App\Services\Mapuche\PeriodoFiscalService;
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

    private $categoryUpdateService;
    private $periodoFiscalService;


    public function boot(PeriodoFiscalService $periodoFiscalService, CategoryUpdateServiceInterface $categoryUpdateService): void
    {
        $this->periodoFiscalService = $periodoFiscalService;
        $this->categoryUpdateService = $categoryUpdateService;

    }
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
            ->where('codc_dedic', '=', 'NODO')
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
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();

        if (!$periodoFiscal['year'] || !$periodoFiscal['month']) {
            $this->addError('periodo_fiscal', 'Debe seleccionar un período fiscal antes de actualizar.');
            return;
        }
        // dd($this->previewData);
        foreach ($this->previewData as $codc_categ => $data) {
            /**
             * Obtiene la instancia de la categoría Dh11 con el código de categoría especificado.
             *
             * @param string $codc_categ El código de categoría a buscar.
             * @return Dh11|null La instancia de la categoría Dh11 si se encuentra, de lo contrario null.
             */
            $categoria = Dh11::where('codc_categ','=', $codc_categ)->first();
            // Verifica si se encontró una categoría con el código de categoría especificado.
            if ($categoria) {
                $this->categoryUpdateService->updateCategoryWithHistory(
                    $categoria,
                    $this->porcentaje,
                    $periodoFiscal
                );
            }
        }

        Notification::make()
            ->title('Cambios confirmados')
            ->success()
            ->send();

        $this->previewData = collect();
        $this->showConfirmButton = false;
        $this->porcentaje = null;
    }

    public function cancelarCambios()
    {
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
