<?php

namespace App\Filament\Embargos\Resources\EmbargoResource\Pages;

use Filament\Forms\Form;
use Livewire\Attributes\On;
use App\Models\Mapuche\Dh22;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Http\RedirectResponse;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use App\Traits\DisplayResourceProperties;
use Livewire\Features\SupportRedirects\Redirector;
use App\Filament\Embargos\Resources\EmbargoResource;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;



/**
 * @property mixed $form
 */
class ConfigureEmbargoParameters extends Page implements HasForms
{
    use DisplayResourceProperties;

    protected static string $resource = EmbargoResource::class;
    protected static ?string $title = 'Configurar Parametros de Embargo';
    protected static ?string $slug = 'Parametros';
    protected static string $view = 'filament.resources.embargo-resource.pages.configure-embargo-parameters';

    public int $nroLiquiDefinitiva = 0;
    public int $nroLiquiProxima = 0;
    public array $nroComplementarias = [];
    public bool $insertIntoDh25 = false;
    public array $data;
    protected array $periodoFiscal = [];



    public function mount(): void
    {
        $embargoResource = new EmbargoResource();

        $this->data = $embargoResource->getPropertiesToDisplay();
        foreach ($this->data as $key => $value) {
            if ($key === 'periodoFiscal') {
                $this->periodoFiscal = [
                    'periodoFiscal' => $value
                ];
            } else {
                $this->$key = $value;
            }
        }
        Log::info('ConfigureEmbargoParameters booted', $this->data);
        dump($this->periodoFiscal);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(static::addPropertiesToFormSchema([
                Select::make('nroLiquiDefinitiva')
                    ->label('Liquidación Definitiva')
                    ->options(
                        Dh22::getLiquidacionesForWidget()
                            ->when($this->periodoFiscal, function ($query) {
                                return $query->whereRaw("CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) = ?", [
                                    $this->periodoFiscal['periodoFiscal']['year'] . str_pad($this->periodoFiscal['periodoFiscal']['month'], 2, '0', STR_PAD_LEFT)
                                ]);
                            })
                            ->pluck('desc_liqui', 'nro_liqui')
                    )
                    ->required(),
                TextInput::make('nroLiquiProxima')
                    ->required()
                    ->numeric(),
                Select::make('nroComplementarias')
                    ->label('Liquidaciones Complementarias')
                    ->multiple()
                    ->options(
                        Dh22::getLiquidacionesForWidget()
                            ->when($this->periodoFiscal, function ($query) {
                                return $query->whereRaw("CONCAT(per_liano, LPAD(per_limes::text, 2, '0')) = ?", [
                                    $this->periodoFiscal['periodoFiscal']['year'] . str_pad($this->periodoFiscal['periodoFiscal']['month'], 2, '0', STR_PAD_LEFT)
                                ]);
                            })
                            ->pluck('desc_liqui', 'nro_liqui')
                    ),
                Toggle::make('insertIntoDh25')
                    ->label('Insertar en DH25'),
            ]))
            ->columns(2);
    }



    public function submit(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        // Lógica para guardar los parámetros y actualizar los datos
        EmbargoResource::actualizarDatos($data);


        // Redirigir a la página de listado
        return redirect()->to(EmbargoResource::getUrl())->with('success', 'Configuración guardada exitosamente');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PeriodoFiscalSelectorWidget::class,
        ];
    }


    protected function getDefaultProperties(): array
    {
        return [
            'nroLiquiProxima' => 0,
            'nroComplementarias' => [],
            'nroLiquiDefinitiva' => 1,
            'insertIntoDh25' => false,
        ];
    }

    #[On('updated-periodo-fiscal')]
    public function updatedPeriodoFiscal(array $periodoFiscal): void
    {
        $instance = new EmbargoResource();
        $currentProperties = $instance->getPropertiesToDisplay();
        $this->periodoFiscal = [
            'periodoFiscal' => $periodoFiscal,
        ];
        // dd($this->periodoFiscal);
        $updatedProperties = array_merge($currentProperties, $this->periodoFiscal);
        $instance->setPropertyValues($updatedProperties);
        $this->dispatch('propertiesUpdated', $updatedProperties);
    }
}
