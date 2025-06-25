<?php

namespace App\Filament\Afip\Handlers;

use Illuminate\Support\Facades\Log;
use App\Services\SicossControlService;
use Filament\Notifications\Notification;
use App\Services\Mapuche\PeriodoFiscalService;

class SicossControlActionHandler
{
    public function __construct(
        protected SicossControlService $controlService,
        protected PeriodoFiscalService $periodoFiscalService
    ) {}

    /**
     * Ejecuta un control específico siguiendo el patrón común
     */
    public function ejecutarControl(
        string $tipoControl,
        object $livewire,
        ?callable $customLogic = null
    ): array {
        try {
            // Estado de loading
            $livewire->loading = true;

            // Obtener período fiscal
            $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
            $year = $periodoFiscal['year'];
            $month = $periodoFiscal['month'];

            Log::info("Iniciando control de {$tipoControl}", [
                'year' => $year,
                'month' => $month,
                'connection' => $livewire->getConnectionName()
            ]);

            // Configurar servicio
            $this->controlService->setConnection($livewire->getConnectionName());

            // Ejecutar lógica específica o usar la lógica personalizada
            $resultados = $customLogic
                ? $customLogic($this->controlService, $year, $month)
                : $this->ejecutarControlEspecifico($tipoControl, $year, $month);

            // Post-procesamiento común
            $this->postProcesamiento($livewire, $tipoControl, $year, $month, $resultados);

            return $resultados;

        } catch (\Exception $e) {
            $this->manejarError($e, $tipoControl);
            throw $e;
        } finally {
            $livewire->loading = false;
        }
    }

    /**
     * Ejecuta el control específico según el tipo
     */
    protected function ejecutarControlEspecifico(string $tipo, int $year, int $month): array
    {
        return match ($tipo) {
            'aportes' => $this->controlService->ejecutarControlAportes($year, $month),
            'contribuciones' => $this->controlService->ejecutarControlContribuciones($year, $month),
            'cuils' => $this->controlService->ejecutarControlCuils($year, $month),
            'conceptos' => ['status' => 'completed'], // Placeholder
            'conteos' => ['status' => 'completed'], // Placeholder
            default => throw new \InvalidArgumentException("Tipo de control no válido: {$tipo}")
        };
    }


    /**
     * Realiza el post-procesamiento después de ejecutar un control SICOSS
     *
     * Este método se encarga de las tareas de limpieza y notificación que deben
     * realizarse después de completar cualquier control de SICOSS, incluyendo:
     * - Invalidación de caché para forzar actualización de datos
     * - Recarga del resumen en el componente Livewire
     * - Notificación de éxito al usuario
     *
     * @param object $livewire Instancia del componente Livewire que ejecutó el control
     * @param string $tipoControl Tipo de control ejecutado (aportes, contribuciones, cuils, etc.)
     * @param int $year Año del período fiscal procesado
     * @param int $month Mes del período fiscal procesado
     * @param array $resultados Resultados obtenidos del control ejecutado
     * @return void
     */
    protected function postProcesamiento(
        object $livewire,
        string $tipoControl,
        int $year,
        int $month,
        array $resultados
    ): void {
        // Invalidar caché para forzar actualización de estadísticas
        cache()->forget('sicoss_resumen_stats');

        // Recargar resumen en el componente Livewire
        $livewire->cargarResumen();

        // Notificación de éxito con formato de período
        Notification::make()
            ->success()
            ->title(ucfirst($tipoControl) . ' Control Ejecutado')
            ->body("Se completó el control de {$tipoControl} para el período {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT))
            ->send();
    }


    /**
     * Maneja y registra errores que ocurren durante la ejecución de controles SICOSS
     *
     * Este método centraliza el manejo de errores para todos los tipos de controles
     * de SICOSS, proporcionando:
     * - Registro detallado del error en los logs del sistema
     * - Notificación visual al usuario sobre el error ocurrido
     * - Persistencia de la notificación para asegurar visibilidad
     *
     * @param \Exception $e Excepción capturada durante la ejecución del control
     * @param string $tipoControl Tipo de control que falló (aportes, contribuciones, cuils, etc.)
     * @return void
     */
    protected function manejarError(\Exception $e, string $tipoControl): void
    {
        Log::error("Error en control de {$tipoControl}", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        Notification::make()
            ->danger()
            ->title("Error en el control de {$tipoControl}")
            ->body($e->getMessage())
            ->persistent()
            ->send();
    }
}