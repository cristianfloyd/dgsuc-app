<?php

namespace App\Filament\Afip\Actions;

use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\SicossControlService;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

use function sprintf;

use const STR_PAD_LEFT;

class EjecutarControlAportesAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Ejecutar Control de Aportes')
            ->icon('heroicon-o-calculator')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('¿Ejecutar control de aportes?')
            ->modalDescription('Esta acción ejecutará el control específico de aportes para el período fiscal actual.')
            ->modalSubmitActionLabel('Sí, ejecutar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function (): void {
                $this->ejecutarControl();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'ejecutar_control_aportes';
    }

    /**
     * Agrega badge con el período actual.
     */
    public function withPeriodBadge(): static
    {
        return $this->badge(function (): string {
            $livewire = $this->getLivewire();

            return sprintf('%d-%02d', $livewire->year, $livewire->month);
        });
    }

    /**
     * Ejecuta el control de aportes.
     */
    protected function ejecutarControl(): void
    {
        $livewire = $this->getLivewire();

        try {
            // Establecer estado de loading
            $livewire->loading = true;

            // Obtener período fiscal
            $periodoFiscalService = resolve(PeriodoFiscalService::class);
            $periodoFiscal = $periodoFiscalService->getPeriodoFiscal();
            $year = $periodoFiscal['year'];
            $month = $periodoFiscal['month'];

            Log::info('Iniciando control de aportes', [
                'year' => $year,
                'month' => $month,
                'connection' => $livewire->getConnectionName(),
            ]);

            // Ejecutar control
            $service = resolve(SicossControlService::class);
            $service->setConnection($livewire->getConnectionName());
            $resultados = $service->ejecutarControlAportes($year, $month);

            // Invalidar caché y actualizar stats
            cache()->forget('sicoss_resumen_stats');
            $livewire->cargarResumen();

            // Notificación de éxito
            Notification::make()
                ->success()
                ->title('Control de Aportes Ejecutado')
                ->body("Se completó el control de aportes para el período {$year}-" . str_pad((string) $month, 2, '0', STR_PAD_LEFT))
                ->send();
        } catch (Exception $e) {
            Log::error('Error en control de aportes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('Error en el control de aportes')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        } finally {
            $livewire->loading = false;
        }
    }
}
