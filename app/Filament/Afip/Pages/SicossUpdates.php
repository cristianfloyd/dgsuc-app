<?php

declare(strict_types=1);

namespace App\Filament\Afip\Pages;

use Filament\Pages\Page;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Services\Afip\SicossUpdateService;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Models\Mapuche\Dh22;
use Filament\Widgets\MultipleIdLiquiSelector;

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
            $liquidaciones = Dh22::periodoFiscal($this->year, $this->month)
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
                ->label('Ejecutar Actualizaciones')
                ->action('runUpdates')
                ->disabled($this->isProcessing)
                ->requiresConfirmation()
                ->modalDescription('¿Está seguro que desea ejecutar las actualizaciones SICOSS para el período ' . 
                    $this->year . '-' . str_pad((string)$this->month, 2, '0', STR_PAD_LEFT) . 
                    '? Este proceso puede tomar varios minutos.')
        ];
    }
}
