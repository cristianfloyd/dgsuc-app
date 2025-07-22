<?php

namespace App\Livewire\Filament\Reportes\Components;

use App\Models\Reportes\BloqueosDataModel;
use App\Services\Reportes\BloqueosProcessService;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BloqueosProcessor extends Component
{
    /**
     * Registro individual a procesar.
     */
    public ?BloqueosDataModel $registro = null;

    /**
     * Colección de registros para procesamiento masivo.
     */
    public ?Collection $registros = null;

    /**
     * Almacena los resultados del procesamiento.
     */
    public ?Collection $resultados = null;

    /**
     * Indicador de procesamiento en curso.
     */
    public bool $isProcessing = false;

    public function mount(?BloqueosDataModel $registro = null, ?Collection $records = null): void
    {
        $this->registro = $registro;
        $this->registros = $records;
    }

    /**
     * Inicia el procesamiento de bloqueos.
     *
     * @return void
     */
    public function procesarBloqueos(): void
    {
        $this->isProcessing = true;

        try {
            $service = app(BloqueosProcessService::class);

            // Procesar según el caso
            if ($this->registro instanceof BloqueosDataModel) {
                // Procesamiento individual
                $resultado = $service->procesarRegistro($this->registro);
                $this->resultados = collect([$resultado]);
            } elseif ($this->registros instanceof Collection) {
                // Procesamiento masivo de registros seleccionados
                $this->resultados = $this->registros
                    ->filter(fn ($reg) => $reg instanceof BloqueosDataModel)
                    ->map(fn ($registro) => $service->procesarRegistro($registro));
            } else {
                // Procesamiento de todos los registros pendientes
                $this->resultados = $service->procesarBloqueos();
            }


            $this->guardarResultadosEnCache();
            $this->notificarResultados();
        } catch (\Exception $e) {
            $this->manejarError($e);
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Obtiene los resultados del cache o procesa nuevamente.
     *
     * @return Collection
     */
    public function getResultados(): Collection
    {
        return Cache::remember(
            $this->getCacheKey(),
            now()->addHour(),
            fn () => $this->resultados ?? collect(),
        );
    }

    /**
     * Genera estadísticas del procesamiento.
     *
     * @return array
     */
    public function getEstadisticas(): array
    {
        $resultados = $this->getResultados();

        return [
            'total' => $resultados->count(),
            'exitosos' => $resultados->where('success', true)->count(),
            'fallidos' => $resultados->where('success', false)->count(),
            'por_tipo' => $resultados->groupBy('tipo_bloqueo')
                ->map(fn ($grupo) => $grupo->count()),
        ];
    }

    public function render()
    {
        Log::info('BloqueosProcessor render');
        return view('livewire.filament.reportes.components.bloqueos-processor', [
            'estadisticas' => $this->getEstadisticas(),
        ]);
    }

    private function guardarResultadosEnCache(): void
    {
        Cache::put(
            $this->getCacheKey(),
            $this->resultados,
            now()->addHour(),
        );
    }

    private function getCacheKey(): string
    {
        return 'bloqueos_resultados_' . auth()->id();
    }

    private function notificarResultados(): void
    {
        $stats = $this->getEstadisticas();

        Notification::make()
            ->title('Proceso completado')
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
}
