<?php

declare(strict_types=1);

namespace App\Filament\Afip\Pages;

use App\Data\PeriodoFiscalData;
use Filament\Pages\Page;
use Livewire\Attributes\On;
use App\Models\Mapuche\Dh22;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use App\Services\Afip\SicossUpdateService;
use App\Services\Afip\SicossCpto205Service;
use Filament\Widgets\MultipleIdLiquiSelector;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\Afip\SicossEmbarazadasService;
use App\Services\Afip\SicossActividadUpdateService;

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
    public ?array $selectedliquiDefinitiva = null;
    public PeriodoFiscalData $periodoFiscal;

    protected PeriodoFiscalService $periodoFiscalService;
    protected SicossEmbarazadasService $sicossEmbarazadasService;
    protected SicossActividadUpdateService $sicossActividadUpdateService;
    protected SicossCpto205Service $sicossCpto205Service;

    public function boot(
        PeriodoFiscalService $periodoFiscalService,
        SicossEmbarazadasService $sicossEmbarazadasService,
        SicossActividadUpdateService $sicossActividadUpdateService,
        SicossCpto205Service $sicossCpto205Service
    ): void {
        $this->periodoFiscalService = $periodoFiscalService;
        $this->sicossEmbarazadasService = $sicossEmbarazadasService;
        $this->sicossActividadUpdateService = $sicossActividadUpdateService;
        $this->sicossCpto205Service = $sicossCpto205Service;
    }

    public function mount()
    {
        // Obtener el período fiscal actual
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscalFromDatabase();

        $this->year = $periodoFiscal['year'];
        $this->month = $periodoFiscal['month'];

        // Simulación de resultado de actualización
        // $this->updateResults = [
        //     'status' => 'warning', // Prueba también con 'error' y 'warning'
        //     'message' => 'Simulación: Se actualizaron 42 registros de cod_act.',
        //     'details' => [
        //         'registros_afectados' => 42,
        //         'tiempo' => '0.5s',
        //         'usuario' => 'admin',
        //     ],
        // ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PeriodoFiscalSelectorWidget::class,
        ];
    }

    protected function executeServiceMethod(callable $serviceMethod, string $successTitle, string $logContext = 'actualización SICOSS'): void
    {
        $this->isProcessing = true;
        
        try {
            $this->updateResults = $serviceMethod();

            // Manejo flexible de diferentes tipos de respuesta
            $status = $this->updateResults['status'] ?? 'error';
            $message = $this->updateResults['message'] ?? 'Sin mensaje';

            switch ($status) {
                case 'success':
                    Notification::make()
                        ->title($successTitle)
                        ->success()
                        ->body($message)
                        ->send();
                    break;
                
                case 'warning':
                    Notification::make()
                        ->title('Advertencia')
                        ->warning()
                        ->body($message)
                        ->send();
                    break;
                
                default:
                    Notification::make()
                        ->title('Error en la actualización')
                        ->danger()
                        ->body($message)
                        ->send();
                    break;
            }
        } catch (\Throwable $th) {
            Log::error("Error en {$logContext}", [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error')
                ->danger()
                ->body($th->getMessage())
                ->send();
        } finally {
            $this->isProcessing = false;
        }
    }

    #[On('fiscalPeriodUpdated')]
    public function handlePeriodoFiscalUpdated(): void
    {
        // Obtener el período fiscal de la sesión
        $periodoFiscalData = $this->periodoFiscalService->getPeriodoFiscal();

        $this->year = (int)$periodoFiscalData['year'];
        $this->month = (int)$periodoFiscalData['month'];

        // Limpiar resultados anteriores
        $this->updateResults = [];

        // Obtener las liquidaciones para el nuevo período fiscal
        $liquidaciones = Dh22::FilterByYearMonth($this->year, $this->month)
            ->generaImpositivo()
            ->pluck('nro_liqui', 'desc_liqui')
            ->map(fn($nro, $desc) => "#{$nro} - {$desc}")
            ->toArray();

        // Actualizar la propiedad selectedIdLiqui
        $this->selectedIdLiqui = $liquidaciones;

        // Determinar la liquidación definitiva (ejemplo: la última liquidación)
        $this->selectedliquiDefinitiva = Dh22::FilterByYearMonth($this->year, $this->month)
            ->generaImpositivo()
            ->definitiva()
            ->pluck('nro_liqui', 'desc_liqui')
            ->map(fn($nro, $desc) => "#{$nro} - {$desc}")
            ->toArray();

        // Notificar al usuario
        Notification::make()
            ->title('Período fiscal actualizado')
            ->body("Período actual: {$this->year}-{$this->month}")
            ->success()
            ->send();
    }

    public function runUpdates(): void
    {
        $this->executeServiceMethod(
            serviceMethod: function () {
                // Obtener las liquidaciones con sino_genimp = true para el período fiscal seleccionado
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
                    return ['status' => 'warning', 'message' => 'Sin liquidaciones'];
                }

                $service = app()->make(SicossUpdateService::class);
                return $service->executeUpdates($liquidaciones);
            },
            successTitle: 'Actualización DHA8 completada',
            logContext: 'actualización SICOSS DHA8'
        );
    }

    /**
     * Ejecuta la actualización de datos de embarazadas en SICOSS
     */
    public function runEmbarazadasUpdate(): void
    {
        $this->executeServiceMethod(
            serviceMethod: function () {
                // Obtener las liquidaciones para el período fiscal seleccionado
                $liquidaciones = Dh22::FilterByYearMonth($this->year, $this->month)
                    ->generaImpositivo()
                    ->definitiva()
                    ->pluck('nro_liqui')
                    ->toArray();

                if (empty($liquidaciones)) {
                    return [
                        'status' => 'warning', 
                        'message' => "No se encontraron liquidaciones que generen datos impositivos para el período {$this->year}-{$this->month}"
                    ];
                }

                return $this->sicossEmbarazadasService->actualizarEmbarazadas([
                    'year' => $this->year,
                    'month' => $this->month,
                    'liquidaciones' => $liquidaciones,
                    'nro_liqui' => $liquidaciones[0]
                ]);
            },
            successTitle: 'Actualización de embarazadas completada',
            logContext: 'actualización de embarazadas'
        );
    }

    public function runActividadUpdate(): void
    {
        $this->executeServiceMethod(
            serviceMethod: fn() => $this->sicossActividadUpdateService->actualizarCodAct(),
            successTitle: 'Actualización de Actividad completada',
            logContext: 'actualización de actividad'
        );
    }

    /**
     * Ejecuta la actualización de datos del concepto 205 en SICOSS
     */
    public function runConcepto205Update(): void
    {
        $this->executeServiceMethod(
            serviceMethod: fn() => $this->sicossCpto205Service->actualizarCpto205(),
            successTitle: 'Actualización de concepto 205 completada',
            logContext: 'actualización de concepto 205'
        );
    }

    protected function getActions(): array
    {
        return [
            Action::make('show_help')
                ->label(fn() => $this->isHelpVisible ? 'Ocultar Ayuda' : 'Mostrar Ayuda')
                ->icon(fn() => $this->isHelpVisible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                ->action(function () {
                    $this->isHelpVisible = !$this->isHelpVisible;
                }),

            Action::make('run_updates')
                ->label('Update Dha8')
                ->action('runUpdates')
                ->disabled($this->isProcessing)
                ->requiresConfirmation()
                ->modalDescription('¿Está seguro que desea ejecutar las actualizaciones SICOSS para el período ' .
                    $this->year . '-' . str_pad((string)$this->month, 2, '0', STR_PAD_LEFT) .
                    '? Este proceso puede tomar varios minutos.'),

            Action::make('run_embarazadas_update')
                ->label('Actualizar Embarazadas')
                ->color('warning')
                ->icon('heroicon-o-user-group')
                ->action('runEmbarazadasUpdate')
                ->disabled($this->isProcessing)
                ->requiresConfirmation()
                ->modalHeading('Actualizar Situación de Embarazadas')
                ->modalDescription('¿Está seguro que desea actualizar la situación de revista de embarazadas para el período ' .
                    $this->year . '-' . str_pad((string)$this->month, 2, '0', STR_PAD_LEFT) .
                    '? Este proceso actualizará los códigos de situación de revista para las agentes con licencia por embarazo.'),

            Action::make('run_actividad_update')
                ->label('Actualizar Actividad')
                ->color('info')
                ->icon('heroicon-o-briefcase')
                ->action('runActividadUpdate')
                ->disabled($this->isProcessing)
                ->requiresConfirmation()
                ->modalHeading('Actualizar Código de Actividad')
                ->modalDescription('¿Está seguro que desea actualizar los códigos de actividad? Este proceso es irreversible y puede afectar datos de AFIP.'),

            Action::make('run_concepto205_update')
                ->label('Actualizar Concepto 205')
                ->color('success')
                ->icon('heroicon-o-currency-dollar')
                ->action('runConcepto205Update')
                ->disabled($this->isProcessing)
                ->requiresConfirmation()
                ->modalHeading('Actualizar Concepto 205')
                ->modalDescription('Accion en modo de prueba. No se realizará la actualización.')
                // ->modalDescription('¿Está seguro que desea actualizar los datos del concepto 205? Este proceso creará una tabla temporal con los montos calculados para los agentes que tienen el concepto 789 y 205.')
        ];
    }
}
