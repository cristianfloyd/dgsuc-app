<?php

namespace App\Filament\Resources\Dh11Resource\Widgets;

use App\Models\Dh11;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use Filament\Widgets\Widget;
use App\Services\Dh11Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Services\Dh11RestoreService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Contracts\HasForms;
use App\Traits\CategoriasConstantTrait;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Services\Mapuche\EscalafonService;
use Filament\Forms\Components\Actions\Action;
use App\Services\Mapuche\PeriodoFiscalService;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Contracts\CategoryUpdateServiceInterface;

class ActualizarImppBasicWidget extends Widget implements HasForms
{
    use InteractsWithForms, CategoriasConstantTrait;
    protected static string $view = 'filament.resources.dh11-resource.widgets.actualizar-impp-basic-widget';


    public ?float $porcentaje = null;
    public Collection $previewData;
    public bool $showConfirmButton = false;
    public ?string $codigoescalafon = null;

    private $categoryUpdateService;
    private $periodoFiscalService;
    private $periodoFiscal;
    private $dh11RestoreService;
    private $dh11Service;
    private $escalafonService;

    public function boot(
        PeriodoFiscalService $periodoFiscalService,
        CategoryUpdateServiceInterface $categoryUpdateService,
        Dh11RestoreService $dh11RestoreService,
        Dh11Service $dh11Service,
        EscalafonService $escalafonService): void
    {
        $this->periodoFiscalService = $periodoFiscalService;
        $this->categoryUpdateService = $categoryUpdateService;
        $this->dh11RestoreService = $dh11RestoreService;
        $this->dh11Service = $dh11Service;
        $this->escalafonService = $escalafonService;
    }
    public function mount(): void
    {
        $this->form->fill();
        $this->periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
        $this->previewData = collect();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('codigoescalafon')->label('Escalafon')
                    ->options(function () {
                        $codc_categs = collect(['TODO' => 'Todos'])
                            ->merge(
                                $this->escalafonService->getEscalafones()
                            )
                            ->merge([
                                'DOC2' => 'Preuniversitario',
                                'DOCS' => 'Docente Secundario',
                                'DOCU' => 'Docente Universitario',
                                'AUTU' => 'Autoridad Universitario',
                                'AUTS' => 'Autoridad Secundario',
                            ]);
                        return $codc_categs;
                })
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $codc_categs = match ($state) {
                        'DOCE' => array_merge(self::CATEGORIAS['DOCS'],self::CATEGORIAS['DOCU']), // es la suma de DOCS + DOCU
                        'DOCS' => self::CATEGORIAS['DOCS'],
                        'DOC2' => self::CATEGORIAS['DOC2'],
                        'DOCU' => self::CATEGORIAS['DOCU'],
                        'AUTU' => self::CATEGORIAS['AUTU'],
                        'AUTS' => self::CATEGORIAS['AUTS'],
                        'NODO' => self::CATEGORIAS['NODO'],
                        'TODO' => $this->dh11Service->getAllCodcCateg(),
                        default => Dh11::where('codigoescalafon', $state)->pluck('codc_categ')->toArray(),
                    };
                    session(['selected_codc_categs' => $codc_categs]);
                }),
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
                            ->tooltip('Establecer el porcentaje de incremento')
                            ->action('previsualizar'),
                    ]),
                Actions::make([
                    Action::make('restore')->label('Restaurar')->icon('heroicon-m-arrow-path')
                        ->color('info')
                        ->badge(function(){
                             // Verificar si $this->periodoFiscal es válido antes de acceder a sus índices
                            if (isset($this->periodoFiscal['year']) && isset($this->periodoFiscal['month'])) {
                                $year = $this->periodoFiscal['year'];
                                $month = $this->periodoFiscal['month'];
                                return "{$year}-{$month}";
                            }
                            $year = $this->periodoFiscalService->getPeriodoFiscal()['year'];
                            $month = $this->periodoFiscalService->getPeriodoFiscal()['month'];
                            return "{$year}-{$month}"; // Valor por defecto en caso de que no esté inicializado
                        })
                        ->requiresConfirmation()
                        ->action('restoreData'),
                ])
                ->alignCenter()
                ->verticallyAlignEnd()
            ])
            ->columns(3)
            ;
    }

    public function previsualizar(): void
    {
        $selectedCategs = Session('selected_codc_categs');

        $this->validate();


        $factor = 1 + $this->porcentaje / 100;

        $this->previewData = Dh11::select('codc_categ', 'impp_basic')
            ->where('impp_basic', '>', 0)
            ->whereIn('codc_categ', $selectedCategs)
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
        // Verificar si $this->periodoFiscal es válido
        $periodoFiscal =  $this->periodoFiscalService->getPeriodoFiscal();
        Log::debug("Periodo fiscal en confirmarCambios: {$periodoFiscal['year']} - {$periodoFiscal['month']}");


        if (!$periodoFiscal['year'] || !$periodoFiscal['month']) {
            $this->addError('periodo_fiscal', 'Debe seleccionar un período fiscal antes de actualizar.');
            return;
        }

        try {
            foreach ($this->previewData as $data) {
                $codc_categ = trim($data['codc_categ']);
                $categoria = Dh11::where('codc_categ', $codc_categ)->first();

                if ($categoria) {
                    $this->categoryUpdateService->updateCategoryWithHistory(
                        $categoria,
                        $this->porcentaje,
                        $periodoFiscal
                    );
                } else {
                    Log::warning("No se encontró la categoría con código: {$codc_categ}");
                }
            }

            $this->addNotification('Cambios aplicados correctamente');
            $this->resetForm();

        } catch (\Exception $e) {
            Log::error('Error al confirmar cambios: ' . $e->getMessage());
            $this->addError('general', 'Ocurrió un error al aplicar los cambios. Por favor, inténtelo de nuevo.');
        }
    }

    /**
     * Restaura los datos de las categorías de la tabla Dh11 para un período fiscal específico.
     *
     * Este método se encarga de restaurar los datos de las categorías de la tabla Dh11 para un período fiscal determinado.
     * Si no se selecciona un período fiscal válido, se mostrará un mensaje de error.
     * En caso de éxito, se mostrará una notificación indicando que las categorías se han restaurado correctamente.
     * Si ocurre un error durante el proceso, se mostrará un mensaje de error general.
     */
    public function restoreData(): void
    {
        try {
            $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();

            if (!$periodoFiscal['year'] || !$periodoFiscal['month']) {
                $this->addError('periodo_fiscal', 'Debe seleccionar un período fiscal antes de restaurar.');
                return;
            }

            $this->dh11RestoreService->restoreFiscalPeriod(
                $periodoFiscal['year'],
                $periodoFiscal['month']
            );

            $this->addNotification('Categorías restauradas correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al restaurar datos: ' . $e->getMessage());
            $this->addError('general', 'Ocurrió un error al restaurar los datos. Por favor, inténtelo de nuevo.');
        }
    }

    #[On('updated-periodo-fiscal')]
    public function updatedPeriodoFiscal($periodoFiscal): void
    {
        Log::debug('Periodo fiscal actualizado en imppWidget:' . json_encode($periodoFiscal));
        $this->resetForm();
        $this->periodoFiscal = $periodoFiscal;
        $this->addNotification('Período fiscal actualizado correctamente.');
    }

    /**
     * Restablece el formulario a su estado inicial.
     */
    public function cancelarCambios()
    {
        $this->resetForm();
    }

    private function addNotification($message)
    {
        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }

    private function resetForm(): void
    {
        $this->form->fill();
        $this->previewData = collect();
        $this->showConfirmButton = false;
        $this->porcentaje = null;
        $this->codigoescalafon = null;
    }


    /**
     * Redondea un número hacia arriba con un número específico de decimales.
     *
     * @param float $number El número a redondear.
     * @param int $precision El número de decimales a mantener.
     * @return float El número redondeado hacia arriba.
     */
    private function round_up($number, $precision = 2)
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
