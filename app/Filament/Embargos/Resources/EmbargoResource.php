<?php

namespace App\Filament\Embargos\Resources;

use App\Filament\Embargos\Resources\EmbargoResource\Pages;
use App\Models\EmbargoProcesoResult;
use App\Services\EmbargoTableService;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Tables\EmbargoTable;
use App\Traits\DisplayResourceProperties;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class EmbargoResource extends Resource
{
    use DisplayResourceProperties;

    // Propiedades públicas del recurso
    public array $periodoFiscal = [];

    public int $nroLiquiProxima = 9;

    public array $nroComplementarias = [];

    public int $nroLiquiDefinitiva = 1;

    public bool $insertIntoDh25 = false;

    protected static ?string $model = EmbargoProcesoResult::class;

    protected static ?string $navigationGroup = 'Liquidaciones';

    protected static ?string $modelLabel = 'Embargo';

    protected static ?string $slug = 'embargos';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected EmbargoTable $embargoTable;

    protected ?PeriodoFiscalService $periodoFiscalService = null;

    protected EmbargoTableService $tableService;

    public function __construct()
    {
        // Inicializar el servicio de periodo fiscal en el constructor
        $this->periodoFiscalService = app(PeriodoFiscalService::class);
    }

    public function boot(EmbargoTableService $tableService): void
    {
        $this->tableService = $tableService;
        $this->ensureTableExists();
    }

    public function mount(EmbargoTable $embargoTable, PeriodoFiscalService $periodoFiscalService): void
    {
        $this->embargoTable = $embargoTable;
        $this->nroLiquiProxima = 4; // Lógica para determinar este valor
        $this->nroComplementarias = []; // Lógica para determinar este array
        $this->nroLiquiDefinitiva = 0; // Lógica para determinar este valor
        $this->insertIntoDh25 = false; // Lógica para determinar este booleano
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')->label('Nro. Liquidación'),
                TextColumn::make('tipo_embargo')->label('Tipo Embargo'),
                TextColumn::make('nro_legaj')->label('Nro. Legajo'),
                TextColumn::make('remunerativo')->money('ARS'),
                TextColumn::make('no_remunerativo')->money('ARS'),
                TextColumn::make('total')->money('ARS')->formatStateUsing(fn ($state) => $state / 100),
                TextColumn::make('codn_conce')->label('Código Concepto'),
            ])
            ->defaultSort('nro_legaj')
            ->filters([

            ])
            ->actions([

            ])
            ->bulkActions([

            ]);
    }

    public static function actualizarDatos(array $data = []): void
    {
        $instance = new static();
        $instance->setPropertyValues($data);

        // Actualizamos las propiedades del recurso
        $instance->nroLiquiProxima = $data['nroLiquiProxima'] ?? 0;
        $instance->nroComplementarias = $data['nroComplementarias'] ?? [];
        $instance->nroLiquiDefinitiva = $data['nroLiquiDefinitiva'] ?? 0;
        $instance->insertIntoDh25 = $data['insertIntoDh25'] ?? false;
        $instance->periodoFiscal = $data['periodoFiscal'] ?? [];

        // Obtener el periodo fiscal del recurso con validación adicional
        $periodoFiscal = 0;
        if (isset($instance->periodoFiscal, $instance->periodoFiscal['year'], $instance->periodoFiscal['month'])) {
            $year = $instance->periodoFiscal['year'];
            $month = str_pad($instance->periodoFiscal['month'], 2, '0', \STR_PAD_LEFT);
            $periodoFiscal = (int)"{$year}{$month}";
        } else {
            // Obtener el período fiscal del servicio como fallback
            $periodoFiscalData = app(PeriodoFiscalService::class)->getPeriodoFiscalFromDatabase();
            $year = $periodoFiscalData['year'] ?? date('Y');
            $month = $periodoFiscalData['month'] ?? date('m');
            $periodoFiscal = (int)("{$year}" . str_pad($month, 2, '0', \STR_PAD_LEFT));
            Log::warning('Se utilizará el período fiscal del servicio como valor predeterminado', ['periodoFiscal' => $periodoFiscal]);
        }

        try {
            // Ejecutar el proceso de embargo a través del modelo
            $results = EmbargoProcesoResult::ejecutarProcesoEmbargo(
                $instance->nroComplementarias,
                $instance->nroLiquiDefinitiva,
                $instance->nroLiquiProxima,
                $instance->insertIntoDh25,
                $periodoFiscal,
            );

            // Mostrar mensaje de éxito con detalles
            Notification::make()
                ->title('Proceso de embargos completado')
                ->success()
                ->body('Se procesaron ' . \count($results) . ' registros de embargos')
                ->send();

            // Remover el dd para producción
            // dd($results);
        } catch (\Exception $e) {
            // Notificar error
            Notification::make()
                ->title('Error al procesar embargos')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmbargos::route('/'),
            'dashboard' => Pages\DashboardEmbargo::route('/admin'),
            'configure' => Pages\ConfigureEmbargoParameters::route('/configure'),
        ];
    }

    public static function getActions(): array
    {
        return [
            Action::make('actualizar')
                ->label('Actualizar Datos')
                ->action(fn () => self::actualizarDatos())
                ->button(),
        ];
    }

    /**
     * Obtiene las propiedades a mostrar y sus valores actuales.
     * Este método utiliza la implementación del trait, que maneja el caché.
     *
     * @return array
     */
    public function getPropertiesToDisplay(): array
    {
        // método del trait que maneja el caché
        return $this->getCachedProperties();
    }

    #[On('updated-periodo-fiscal')]
    public function updatedPeriodoFiscal(array $periodoFiscal): void
    {
        $this->periodoFiscal = $periodoFiscal;
        Notification::make('Periodo Fiscal Actualizado');

    }

    protected function getListeners(): array
    {
        return [
            'propertiesUpdated' => 'handlePropertiesUpdated',
        ];
    }

    /**
     * Define las propiedades por defecto del recurso.
     * Este método es requerido por el trait DisplayResourceProperties.
     *
     * @return array
     */
    protected function getDefaultProperties(): array
    {
        // Asegurarse de que periodoFiscalService esté disponible
        if ($this->periodoFiscalService === null) {
            $this->periodoFiscalService = app(PeriodoFiscalService::class);
        }

        return [
            'nroLiquiProxima' => $this->nroLiquiProxima,
            'nroComplementarias' => $this->nroComplementarias,
            'nroLiquiDefinitiva' => $this->nroLiquiDefinitiva,
            'insertIntoDh25' => $this->insertIntoDh25,
            'periodoFiscal' => $this->periodoFiscalService->getPeriodoFiscalFromDatabase(),
        ];
    }
}
