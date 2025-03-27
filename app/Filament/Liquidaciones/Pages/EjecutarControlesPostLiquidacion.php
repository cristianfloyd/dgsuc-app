namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use App\Services\LiquidacionControlService;

class EjecutarControlesPostLiquidacion extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $title = 'Ejecutar Controles Post-Liquidación';

    public $nroLiqui;
    public $resultados = [];

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('nroLiqui')
                ->label('Número de Liquidación')
                ->required()
                ->options(function() {
                    // Obtener liquidaciones disponibles
                    return [/* ... */];
                }),
            Forms\Components\CheckboxList::make('controles')
                ->label('Controles a Ejecutar')
                ->options([
                    'cargos_liquidados' => 'Control de Cargos Liquidados',
                    'negativos' => 'Control de Negativos',
                    'subrogancias' => 'Control de Subrogancias',
                    // ... más controles
                ]),
        ];
    }

    public function ejecutarControles()
    {
        $service = new LiquidacionControlService();

        foreach ($this->controles as $control) {
            $this->resultados[$control] = match($control) {
                'cargos_liquidados' => $service->controlarCargosLiquidados($this->nroLiqui),
                'negativos' => $service->controlarNegativos($this->nroLiqui),
                // ... más controles
            };
        }
    }
}