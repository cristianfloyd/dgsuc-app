<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;
use App\Models\Reportes\BloqueosDataModel;
use App\Services\Reportes\BloqueosProcessService;

class BloqueosDataProcessor extends Component
{
    /**
     * Registro individual a procesar
     */
    public ?BloqueosDataModel $registro = null;

    /**
     * Colección de registros para procesamiento masivo
     */
    public ?Collection $registros = null;

    /**
     * Almacena los resultados del procesamiento
     */
    public ?Collection $resultados = null;

    /**
     * Indicador de procesamiento en curso
     */
    public bool $isProcessing = false;
    public int $totalRegistros = 0;
    public int $registrosProcesados = 0;
    public float $porcentajeCompletado = 0;
    private BloqueosProcessService $service;
    // El nro_liqui lo obtendremos del primer registro
    private function getNroLiquidacion(): int
    {
        return $this->registros->first()->nro_liqui;
    }


    public function boot(BloqueosProcessService $service): void
    {
        $this->service = $service;
    }

    public function mount(Collection $registros = null): void
    {

        $this->registros = $registros;


        Log::info('BloqueosDataProcessor mounted', [
            'nro_liqui' => $this->getNroLiquidacion(),
            'registros' => $this->registros?->count(),
        ]);

        if ($this->registros) {
            $this->iniciarProcesamiento();
        }
    }

    /**
     * Inicia el procesamiento de bloqueos
     */
    public function iniciarProcesamiento(): void
    {
        Log::info('Iniciando procesamiento de bloqueos');
        $this->isProcessing = true;

        try {
            DB::connection($this->service->getConnectionName())->beginTransaction();

            // Procesamos los registros en chunks
            $this->procesarRegistros();

            DB::connection($this->service->getConnectionName())->commit();

            $this->guardarResultadosEnCache();
            Log::info('Procesamiento de bloqueos finalizado en BloqueosDataProcessor');
            $this->notificarResultados();

        } catch (\Exception $e) {
            DB::connection($this->service->getConnectionName())->rollBack();
            $this->manejarError($e);
            Log::error('Error en procesamiento de bloqueos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isProcessing = false;
        }
    }

    private function crearTablaBackupSiNoExiste(): void
    {
        $this->service->crearTablaBackupSiNoExiste();
    }



    private function procesarRegistros(): void
    {
        $this->resultados = $this->service->procesarBloqueos();
        $this->totalRegistros = $this->registros->count();
        $this->registrosProcesados = $this->resultados->count();
        $this->porcentajeCompletado = ($this->registrosProcesados / $this->totalRegistros) * 100;
    }

    /**
     * Almacena los resultados del procesamiento en cache
     * Utiliza una clave única por usuario para mantener aislados los resultados
     * El tiempo de expiración es de 1 hora
     */
    private function guardarResultadosEnCache(): void
    {
        if (!$this->resultados) {
            return;
        }

        Cache::put(
            $this->getCacheKey(),
            $this->resultados,
            now()->addHour()
        );

        Log::info('Resultados guardados en cache', [
            'key' => $this->getCacheKey(),
            'count' => $this->resultados->count()
        ]);
    }

    /**
     * Refresca los datos en caché con los resultados actuales
     */
    public function refrescarCache(): void
    {
        Cache::forget($this->getCacheKey());
        $this->guardarResultadosEnCache();
    }

    /**
     * Limpia los resultados almacenados en caché
     */
    public function limpiarCache(): void
    {
        Cache::forget($this->getCacheKey());
        $this->resultados = collect();
    }

    /**
     * Verifica si existen resultados en caché
     */
    public function tieneResultadosEnCache(): bool
    {
        return Cache::has($this->getCacheKey());
    }


    private function restaurarBackup(): void
    {
        $this->service->restaurarBackup();
    }

    public function getResultados(): Collection
    {
        return Cache::remember(
            $this->getCacheKey(),
            now()->addHour(),
            fn() => $this->resultados ?? collect()
        );
    }

    public function getEstadisticas(): array
    {
        $resultados = $this->getResultados();
        return [
            'total' => $resultados->count(),
            'exitosos' => $resultados->where('success', true)->count(),
            'fallidos' => $resultados->where('success', false)->count(),
            'por_tipo' => $resultados->groupBy('tipo_bloqueo')
                ->map(fn($grupo) => $grupo->count()),
            'nro_liqui' => $this->getNroLiquidacion()
        ];
    }

    /**
     * Genera una clave única de cache basada en el ID del usuario autenticado
     */
    private function getCacheKey(): string
    {
        // Incluimos nro_liqui en la clave de caché
        return "bloqueos_resultados_{$this->getNroLiquidacion()}_" . auth()->id();
    }

    private function notificarResultados(): void
    {
        $stats = $this->getEstadisticas();
        $nroLiqui = $this->getNroLiquidacion();
        Notification::make()
            ->title("Proceso completado - Liquidación {$nroLiqui}")
            ->body("Total: {$stats['total']}, Exitosos: {$stats['exitosos']}, Fallidos: {$stats['fallidos']}")
            ->success()
            ->send();
    }

    private function manejarError(\Exception $e): void
    {
        Notification::make()
            ->title('Error en el procesamiento')
            ->body($e->getMessage())
            ->danger()
            ->send();
    }



    public function render()
    {
        return view('livewire.bloqueos-data-processor', [
            'estadisticas' => $this->getEstadisticas(),
            'resultados' => $this->getResultados(),
        ]);
    }
}
