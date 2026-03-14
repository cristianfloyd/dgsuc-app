<?php

/** @noinspection PhpUnused */

namespace App\Filament\Reportes\Resources\OrdenDePagos\Pages;

use App\Filament\Reportes\Resources\OrdenDePagos\OrdenDePagos\OrdenDePagoResource;
use App\Filament\Reportes\Resources\OrdenDePagos\Widgets\OrdenPagoStatsWidget;
use App\Models\Reportes\RepOrdenPagoModel;
use App\Services\RepOrdenPagoService;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ListReportes extends ListRecords
{
    public bool $reporteGenerado = false;

    protected static string $resource = OrdenDePagoResource::class;

    protected string $view = 'filament.resources.reporte-resource.pages.list-reportes';

    protected RepOrdenPagoService $ordenPagoService;

    public function boot(RepOrdenPagoService $ordenPagoService): void
    {
        $this->ordenPagoService = $ordenPagoService;
    }

    #[\Override]
    public function mount(): void
    {
        $this->ordenPagoService->ensureTableAndFunction();
        // Verificar si existen registros en la tabla
        $this->reporteGenerado = RepOrdenPagoModel::query()->whereNotNull('nro_liqui')->exists();
        parent::mount();
    }

    public static function getEloquentQuery()
    {
        $repOrdenPagoService = resolve(RepOrdenPagoService::class);
        $allRepOrdenPago = $repOrdenPagoService->getAllRepOrdenPago();


        return $allRepOrdenPago->toQuery();
    }

    #[On('idsLiquiSelected')]
    public function actualizarLiquidacionesSeleccionadas($liquidaciones): void
    {
        session(['idsLiquiSelected' => $liquidaciones]);
        Log::debug('se graba en la session --> isdLiquiSelected', ['state' => $liquidaciones]);
    }

    /**
     * @param $reporteGenerado
     *
     * @return $this
     */
    public function setReporteGenerado(bool $reporteGenerado): static
    {
        $this->reporteGenerado = $reporteGenerado;
        return $this;
    }

    public function generarReporte(): bool
    {
        $selectedLiquidaciones = $this->getLiquidacionesSeleccionadas();


        if ($selectedLiquidaciones === []) {
            Notification::make()
                ->title('Seleccione una liquidación')
                ->warning()
                ->send();
            return false;
        }

        try {
            DB::connection($this->connection)->select('SELECT suc.rep_orden_pago(?)', ['{' . implode(',', $selectedLiquidaciones) . '}']);
            // Notification::make()->title('Reporte generado')->success()->send();
            $this->reporteGenerado = true;
            return true;
        } catch (Exception $e) {
            Log::error('Error al generar el reporte: ' . $e->getMessage());
            Notification::make()->title('Error al generar el reporte')->danger()->send();
            $this->reporteGenerado = false;
            return false;
        }
    }

    public function getLiquidacionesSeleccionadas(): array
    {
        $data = session('idsLiquiSelected', []);
        Log::debug('getLiquidacionesSeleccionadas', ['data' => $data]);
        return $data;
    }

    #[\Override]
    public function getHeaderWidgets(): array
    {
        return [
            OrdenPagoStatsWidget::class,
        ];
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('verOP')
                ->label('Ver OP')
                ->url(route('reporte-orden-pago-pdf'), shouldOpenInNewTab: true)
                ->visible(fn(): bool => $this->reporteGenerado),
            Action::make('generarReporte')
                ->label('Generar Reporte')
                ->icon('heroicon-o-document-currency-dollar')
                ->url('/reportes/orden-de-pagos/crear')
                ->color('success'),
            Action::make('truncate')
                ->label('Limpiar Tabla')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('¿Está seguro de limpiar la tabla?')
                ->modalDescription('Esta acción eliminará todos los registros de la tabla y no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, limpiar tabla')
                // ->modalCancelActionLabel('No, cancelar')
                ->action(function (): void {
                    try {
                        $this->ordenPagoService->truncateTable();

                        Notification::make()
                            ->title('Tabla limpiada exitosamente')
                            ->success()
                            ->send();

                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Error al limpiar la tabla')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

        ];
    }
}
