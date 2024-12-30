<?php /** @noinspection PhpUnused */

namespace App\Filament\Reportes\Resources\OrdenDePagoResource\Pages;

use Exception;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\RepOrdenPagoService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\MultipleIdLiquiSelector;
use App\Filament\Reportes\Resources\OrdenDePagoResource;

class ListReportes extends ListRecords
{
    protected $connection = 'pgsql-liqui';
    protected static string $resource = OrdenDePagoResource::class;
    protected static string $view = 'filament.resources.reporte-resource.pages.list-reportes';

    public bool $reporteGenerado = false;
    protected RepOrdenPagoService $ordenPagoService;

    public function boot(RepOrdenPagoService $ordenPagoService): void
    {
        $this->ordenPagoService = $ordenPagoService;
    }


    public function mount(): void
    {
        $this->ordenPagoService->ensureTableAndFunction();
    }

    public function getHeader(): \Illuminate\Contracts\View\View|null
    {
        if (!$this->tieneTablaYDatos()) {
            Log::info('No hay tabla ni datos para mostrar.', ['connection' => $this->ordenPagoService->getConnectionName()]);
            return null;
        }

        $totales = $this->calcularTotales();

        return view('filament.pages.reportes.orden-pago-header', [
            'totalBruto' => money($totales->total_bruto),
            'totalSueldo' => money($totales->total_sueldo),
            'totalAportes' => money($totales->total_aportes),
            'totalDescuentos' => money($totales->total_descuentos),
            'totalImpGasto' => money($totales->total_imp_gasto),
            'cantidadRegistros' => $totales->cantidad_registros
        ]);
    }

    private function tieneTablaYDatos(): bool
    {
        if (!Schema::connection($this->ordenPagoService->getConnectionName())->hasTable('suc.rep_orden_pago')) {
            Log::info('No hay tabla para mostrar.', ['connection' => $this->ordenPagoService->getConnectionName()]);
            return false;
        }

        return DB::connection($this->ordenPagoService->getConnectionName())->table('suc.rep_orden_pago')->count() > 0;
    }

    private function calcularTotales(): object
    {
        return DB::connection($this->ordenPagoService->getConnectionName())->table('suc.rep_orden_pago')
            ->selectRaw('
                COUNT(*) as cantidad_registros,
                SUM(bruto) as total_bruto,
                SUM(sueldo) as total_sueldo,
                SUM(aportes) as total_aportes,
                SUM(descuentos) as total_descuentos,
                SUM(imp_gasto) as total_imp_gasto
            ')
            ->first();
    }

    public static function getEloquentQuery()
    {
        $repOrdenPagoService = app(RepOrdenPagoService::class);
        $repOrdenPagoRecords = $repOrdenPagoService->getAllRepOrdenPago();


        return $repOrdenPagoRecords->toQuery();
    }



    #[On('idsLiquiSelected')]
    public function actualizarLiquidacionesSeleccionadas($liquidaciones): void
    {
        session(['idsLiquiSelected' => $liquidaciones]);
        Log::debug("se graba en la session --> isdLiquiSelected", ['state' => $liquidaciones]);
    }


    /**
     * @param $reporteGenerado
     * @return $this
     */
    public function setReporteGenerado($reporteGenerado): static
    {
        $this->reporteGenerado = $reporteGenerado;
        return $this;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generarReporte')
                ->label('Generar Reporte')
                ->icon('heroicon-o-document-currency-dollar')
                ->url('/reportes/orden-de-pagos/crear')
                ->color('success'),
            Action::make('truncateTable')
                ->label('Limpiar Tabla')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('¿Está seguro de limpiar la tabla?')
                ->modalDescription('Esta acción eliminará todos los registros de la tabla y no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, limpiar tabla')
                ->modalCancelActionLabel('No, cancelar')
                ->action(function() {
                    try {
                        $this->ordenPagoService->truncateTable();

                        Notification::make()
                            ->title('Tabla limpiada exitosamente')
                            ->success()
                            ->send();

                        $this->refresh();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Error al limpiar la tabla')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })

        ];
    }

    public function generarReporte(): bool
    {
        $selectedLiquidaciones = $this->getLiquidacionesSeleccionadas();


        if (empty($selectedLiquidaciones)) {
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
        Log::debug("getLiquidacionesSeleccionadas", ['data' => $data]);
        return $data;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //MultipleIdLiquiSelector::class,
        ];
    }

}
