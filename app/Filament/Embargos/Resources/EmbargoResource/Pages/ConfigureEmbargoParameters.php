<?php

namespace App\Filament\Embargos\Resources\EmbargoResource\Pages;

use Filament\Forms\Set;
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
use App\Services\Mapuche\PeriodoFiscalService;
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
    }

    public function form(Form $form): Form
    {
        $periodoFiscalActual = $this->periodoFiscal['periodoFiscal'] ?? null;

        return $form
            ->schema(static::addPropertiesToFormSchema([
                Select::make('nroLiquiDefinitiva')
                    ->label('Liquidación Definitiva')
                    ->options(
                        Dh22::getLiquidacionesByPeriodoFiscal($periodoFiscalActual)
                    )
                    ->live()
                    ->required(),
                Select::make('nroLiquiProxima')
                    ->required()
                    ->options(
                        Dh22::getLiquidacionesByPeriodoFiscal($periodoFiscalActual)
                    ),
                Select::make('nroComplementarias')
                    ->label('Liquidaciones Complementarias')
                    ->multiple()
                    ->options(
                        Dh22::getLiquidacionesByPeriodoFiscal($periodoFiscalActual)
                    ),
                Toggle::make('insertIntoDh25')
                    ->label('Insertar en DH25'),
            ]))
            ->columns(2);
    }



    public function submit(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        // Agregar el periodo fiscal a los datos
        if (isset($this->periodoFiscal) && isset($this->periodoFiscal['periodoFiscal'])) {
            $data['periodoFiscal'] = $this->periodoFiscal['periodoFiscal'];

            // Registrar para depuración
            Log::info('Enviando datos con periodo fiscal', [
                'data' => $data,
                'periodoFiscal' => $this->periodoFiscal['periodoFiscal']
            ]);
        } else {
            Log::warning('No se encontró periodo fiscal para agregar a los datos, obteniendo de la sesion');
            // Obtener el periodo fiscal de la sesión
            $periodoFiscalSesion = app( PeriodoFiscalService::class)->getPeriodoFiscal();
            // $periodoFiscalSesion = session('periodo_fiscal');
            Log::debug('Período fiscal obtenido de la sesión: ' , $periodoFiscalSesion);

            if ($periodoFiscalSesion) {
                $data['periodoFiscal'] = $periodoFiscalSesion;
            } else {
                Log::warning('No se encontró periodo fiscal en la sesión');
            }
        }

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
        $updatedProperties = array_merge($currentProperties, $this->periodoFiscal);
        $instance->setPropertyValues($updatedProperties);
        $this->dispatch('propertiesUpdated', $updatedProperties);
    }
}
