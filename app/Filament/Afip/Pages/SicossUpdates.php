<?php

declare(strict_types=1);

namespace App\Filament\Afip\Pages;

use Filament\Pages\Page;
use Livewire\Attributes\On;
use App\Models\Mapuche\Dh22;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use App\Services\Afip\SicossUpdateService;
use Filament\Widgets\MultipleIdLiquiSelector;
use App\Services\Mapuche\PeriodoFiscalService;

class SicossUpdates extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?string $navigationLabel = 'Actualización SICOSS';
    protected static ?string $title = 'Actualización de Datos SICOSS';

    protected static string $view = 'filament.afip.pages.sicoss-updates';

    public array $updateResults = [];
    public bool $isProcessing = false;
    public ?array $selectedIdLiqui = null;
    public $year;
    public $month;
    public bool $isHelpVisible = false;

    protected PeriodoFiscalService $periodoFiscalService;

    public function boot(PeriodoFiscalService $periodoFiscalService): void
    {
        $this->periodoFiscalService = $periodoFiscalService;
    }

    public function mount()
    {
        // Obtener el período fiscal actual
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscalFromDatabase();
        $this->year = $periodoFiscal['year'];
        $this->month = $periodoFiscal['month'];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PeriodoFiscalSelectorWidget::class,
        ];
    }

    #[On('fiscalPeriodUpdated')]
    public function handlePeriodoFiscalUpdated(): void
    {
        // Obtener el período fiscal de la sesión
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
        $this->year = $periodoFiscal['year'];
        $this->month = $periodoFiscal['month'];

        // Limpiar resultados anteriores
        $this->updateResults = [];

        // Notificar al usuario
        Notification::make()
            ->title('Período fiscal actualizado')
            ->body("Período actual: {$this->year}-{$this->month}")
            ->success()
            ->send();
    }

    public function runUpdates(): void
    {
        $this->isProcessing = true;

        try {
            // Obtener las liquidaciones con sino_genimp = true para el período fiscal seleccionado
            // usando los scopes definidos en el modelo Dh22
            $liquidaciones = Dh22::FilterByYearMonth($this->year, $this->month)
                ->generaImpositivo()
                ->pluck('nro_liqui')
                ->toArray();

            if (empty($liquidaciones)) {
                Notification::make()
                    ->title('Sin liquidaciones')
                    ->warning()
                    ->body("No se encontraron liquidaciones que generen datos impositivos para el período {$this->year}-{$this->month}")
                    ->send();
                $this->isProcessing = false;
                return;
            }

            $service = app()->make(SicossUpdateService::class);
            // Pasar las liquidaciones encontradas al servicio
            $this->updateResults = $service->executeUpdates($liquidaciones);

            if ($this->updateResults['status'] === 'success') {
                Notification::make()
                    ->title('Actualización completada')
                    ->success()
                    ->body("Se procesaron " . count($liquidaciones) . " liquidaciones para el período {$this->year}-{$this->month}")
                    ->send();
            } else {
                Notification::make()
                    ->title('Error en la actualización')
                    ->danger()
                    ->body($this->updateResults['message'])
                    ->send();
            }
        } catch (\Exception $e) {
            Log::error('Error en actualización SICOSS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error')
                ->danger()
                ->body($e->getMessage())
                ->send();
        } finally {
            $this->isProcessing = false;
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('run_updates')
                ->label('Update Dha8')
                ->action('runUpdates')
                ->disabled($this->isProcessing)
                ->requiresConfirmation()
                ->modalDescription('¿Está seguro que desea ejecutar las actualizaciones SICOSS para el período ' .
                    $this->year . '-' . str_pad((string)$this->month, 2, '0', STR_PAD_LEFT) .
                    '? Este proceso puede tomar varios minutos.'),
            Action::make('show_help')
                ->label(fn () => $this->isHelpVisible ? 'Ocultar Ayuda' : 'Mostrar Ayuda')
                ->icon(fn () => $this->isHelpVisible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                ->action(function () {
                    $this->isHelpVisible = !$this->isHelpVisible;
                })
        ];
    }

    public function showHelp(): void
    {
        // Este método puede ser usado para mostrar un modal con la ayuda
    }

    protected function getHelpContent(): string
    {
        return <<<HTML
        <h2>Descripción General</h2>
        <p>La herramienta de actualización SICOSS permite a los usuarios actualizar y verificar datos necesarios para la generación de archivos SICOSS. Este proceso asegura que los datos de los agentes estén correctamente categorizados.</p>
        <h3>Acceso al Sistema</h3>
        <ul>
            <li><strong>Ruta de Acceso</strong>: /afip-panel/sicoss-updates</li>
            <li><strong>Navegación</strong>: AFIP > Actualización SICOSS</li>
            <li><strong>Permisos</strong>: Necesitas acceso al panel AFIP para utilizar esta herramienta.</li>
        </ul>
        <h3>Proceso de Actualización</h3>
        <ol>
            <li><strong>Seleccionar Liquidaciones</strong>: Usa el widget de selección para elegir las liquidaciones que deseas procesar. Filtra por período fiscal si es necesario.</li>
            <li><strong>Ejecutar Actualizaciones</strong>: Haz clic en "Ejecutar Actualizaciones" para iniciar el proceso. El sistema determinará automáticamente si debe usar tablas actuales o históricas.</li>
            <li><strong>Revisar Resultados</strong>: Observa el progreso y los resultados detallados. Verifica si hay agentes sin código de actividad.</li>
        </ol>
        <h3>Resultados y Feedback</h3>
        <ul>
            <li><strong>Indicador de Progreso</strong>: Muestra el avance del proceso.</li>
            <li><strong>Resultados Detallados</strong>: Incluye un listado de liquidaciones seleccionadas y agentes sin actividad.</li>
            <li><strong>Notificaciones</strong>: Recibirás mensajes de éxito o error al finalizar el proceso.</li>
        </ul>
        <h3>Consideraciones Técnicas</h3>
        <ul>
            <li><strong>Seguridad</strong>: El acceso está controlado mediante permisos en el panel.</li>
            <li><strong>Performance</strong>: El sistema está optimizado para procesar grandes cantidades de datos de manera eficiente.</li>
        </ul>
        <h3>Soporte</h3>
        <p>Para asistencia adicional, contacta al equipo de soporte técnico a través del panel de ayuda en el sistema.</p>
        HTML;
    }
}
