<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use Filament\Forms\Form;
use App\Models\Mapuche\Dh22;
use Filament\Resources\Pages\Page;
use App\Models\EmbargoProcesoResult;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Http\RedirectResponse;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Traits\DisplayResourceProperties;
use App\Filament\Resources\EmbargoResource;
use Livewire\Features\SupportRedirects\Redirector;


/**
 * @property mixed $form
 */
class ConfigureEmbargoParameters extends Page implements HasForms
{
    use DisplayResourceProperties;

    protected static string $resource = EmbargoResource::class;
    protected static ?string $title = 'Configurar Parametros de Embargo';
    protected static string $view = 'filament.resources.embargo-resource.pages.configure-embargo-parameters';

    public int $nroLiquiProxima = 0;
    public ?array $nroComplementarias = [];
    public int $nroLiquiDefinitiva = 0;
    public bool $insertIntoDh25 = false;
    public mixed $data;
    protected mixed $periodoFiscal = [];

    public static function getPropertiesToDisplay(): array
    {
        return [
            'nroLiquiProxima',
            'nroComplementarias',
            'nroLiquiDefinitiva',
            'insertIntoDh25',
        ];
    }

    public function mount(): void
    {
        // $this->form->fill();
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
                                    $this->periodoFiscal['year'] . str_pad($this->periodoFiscal['month'], 2, '0', STR_PAD_LEFT)
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
                                    $this->periodoFiscal['year'] . str_pad($this->periodoFiscal['month'], 2, '0', STR_PAD_LEFT)
                                ]);
                            })
                            ->pluck('desc_liqui', 'nro_liqui')
                    ),
                Toggle::make('insertIntoDh25')
                    ->label('Insertar en DH25'),
            ]));
        // ->statePath('data'); // Esta línea establece la ruta del estado del formulario a 'data'
    }



    public function submit(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        // Lógica para guardar los parámetros y actualizar los datos
        EmbargoResource::actualizarDatos($data);



        // Actualizar los datos usando el método updateData
        // EmbargoProcesoResult::updateData(
        //     EmbargoResource::$nroComplementarias,
        //     EmbargoResource::$nroLiquiDefinitiva,
        //     EmbargoResource::$nroLiquiProxima,
        //     EmbargoResource::$insertIntoDh25
        // );

        // Redirigir o mostrar un mensaje de éxito
        Notification::make()
            ->title('Configuration saved successfully')
            ->success()
            ->send();

        // Redirigir a la página de listado
        return redirect()->to(EmbargoResource::getUrl())->with('success', 'Configuration saved successfully');
    }
}
