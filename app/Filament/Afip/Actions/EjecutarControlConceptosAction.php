<?php

namespace App\Filament\Afip\Actions;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use App\Services\SicossControlService;
use Filament\Notifications\Notification;
use App\Services\Mapuche\PeriodoFiscalService;
use Illuminate\Support\Facades\View as ViewFacade;
use Filament\Notifications\Actions\Action as NotificationAction;

class EjecutarControlConceptosAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'ejecutar_control_conceptos';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Ejecutar Control de Conceptos')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('¿Ejecutar control de conceptos?')
            ->modalDescription('Esta acción ejecutará el control de conceptos por período fiscal.')
            ->modalSubmitActionLabel('Sí, ejecutar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function (): void {
                $this->ejecutarControl();
            });
    }

    /**
     * Ejecuta el control de conceptos
     */
    protected function ejecutarControl(): void
    {
        $livewire = $this->getLivewire();

        try {
            // Establecer estado de loading
            $livewire->loading = true;

            // Obtener período fiscal
            $periodoFiscalService = app(PeriodoFiscalService::class);
            $periodoFiscal = $periodoFiscalService->getPeriodoFiscal();
            $year = $periodoFiscal['year'];
            $month = $periodoFiscal['month'];

            Log::info('Iniciando control de conceptos', [
                'year' => $year,
                'month' => $month,
                'connection' => $livewire->getConnectionName()
            ]);

            // Ejecutar control
            $service = app(SicossControlService::class);
            $service->setConnection($livewire->getConnectionName());
            $resultados = $service->ejecutarControlConceptos($year, $month);

            // Invalidar caché y actualizar stats
            cache()->forget('sicoss_resumen_stats');
            $livewire->cargarResumen();

            // Crear vista para la notificación (usando la vista existente)
            $viewContent = ViewFacade::make('filament.afip.notifications.control-conceptos', [
                'resultados' => $resultados['resultados'],
                'totalAportes' => $resultados['total_aportes'],
                'totalContribuciones' => $resultados['total_contribuciones'],
                'year' => $year,
                'month' => $month,
            ])->render();

            // Notificación de éxito con detalles
            Notification::make()
                ->success()
                ->title('Control de Conceptos Ejecutado')
                ->body($viewContent)
                ->actions([
                    NotificationAction::make('ver_detalles')
                        ->label('Ver Detalles')
                        ->color('primary')
                        ->icon('heroicon-o-document-text')
                        ->action(fn() => $livewire->activeTab = 'conceptos'),
                ])
                ->persistent()
                ->send();

        } catch (\Exception $e) {
            Log::error('Error en control de conceptos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->danger()
                ->title('Error en el control de conceptos')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        } finally {
            $livewire->loading = false;
        }
    }


    /**
     * Agrega un badge que muestra el período fiscal actual en formato YYYY-MM
     *
     * Este método crea un badge visual que indica el año y mes del período
     * fiscal actual, formateado como "YYYY-MM" (ej: "2024-03"). El badge
     * se obtiene del componente Livewire padre que contiene las propiedades
     * year y month del período fiscal.
     *
     * @return static Retorna la instancia actual del action para permitir method chaining
     */
    public function withPeriodBadge(): static
    {
        return $this->badge(function () {
            $livewire = $this->getLivewire();
            return sprintf('%d-%02d', $livewire->year, $livewire->month);
        });
    }
}