<?php

namespace App\Filament\Embargos\Resources\EmbargoResource\Pages;

use App\Filament\Embargos\Resources\EmbargoResource;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use App\Models\Mapuche\Dh22;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Traits\DisplayResourceProperties;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Pages\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Features\SupportRedirects\Redirector;

/**
 * @property mixed $form
 */
class ConfigureEmbargoParameters extends Page implements HasForms
{
    use DisplayResourceProperties;

    public int $nroLiquiDefinitiva = 0;

    public int $nroLiquiProxima = 0;

    public array $nroComplementarias = [];

    public bool $insertIntoDh25 = false;

    public array $data;

    protected static string $resource = EmbargoResource::class;

    protected static ?string $title = 'Configurar Parametros de Embargo';

    protected static ?string $slug = 'Parametros';

    protected static string $view = 'filament.resources.embargo-resource.pages.configure-embargo-parameters';

    protected array $periodoFiscal = [];

    public function mount(): void
    {
        $embargoResource = new EmbargoResource();

        $this->data = $embargoResource->getPropertiesToDisplay();
        foreach ($this->data as $key => $value) {
            if ($key === 'periodoFiscal') {
                $this->periodoFiscal = [
                    'periodoFiscal' => $value,
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
                        Dh22::getLiquidacionesByPeriodoFiscal($periodoFiscalActual),
                    )
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $set('nroLiquiProxima', $state);
                    })
                    ->required(),
                Select::make('nroLiquiProxima')
                    ->label('Proxima Liquidacion')
                    ->required()
                    ->searchable()
                    ->options(
                        Dh22::getLiquidacionesForWidget($periodoFiscalActual)
                            ->formateadoParaSelect()
                            ->pluck('descripcion_completa', 'nro_liqui')
                    ),
                Select::make('nroComplementarias')
                    ->label('Liquidaciones Complementarias')
                    ->multiple()
                    ->options(
                        Dh22::getLiquidacionesForWidget()
                            ->formateadoParaSelect()
                            ->pluck('descripcion_completa', 'nro_liqui')
                    ),
                Toggle::make('insertIntoDh25')
                    ->label('Insertar en DH20'),
            ]))
            ->columns(2);
    }

    public function submit(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        // Agregar el periodo fiscal a los datos
        if (isset($this->periodoFiscal, $this->periodoFiscal['periodoFiscal'])) {
            $data['periodoFiscal'] = $this->periodoFiscal['periodoFiscal'];

            // Registrar para depuración
            Log::info('Enviando datos con periodo fiscal', [
                'data' => $data,
                'periodoFiscal' => $this->periodoFiscal['periodoFiscal'],
            ]);
        } else {
            Log::warning('No se encontró periodo fiscal para agregar a los datos, obteniendo de la sesion');
            // Obtener el periodo fiscal de la sesión
            $periodoFiscalSesion = app(PeriodoFiscalService::class)->getPeriodoFiscal();
            // $periodoFiscalSesion = session('periodo_fiscal');
            Log::debug('Período fiscal obtenido de la sesión: ', $periodoFiscalSesion);

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
}
