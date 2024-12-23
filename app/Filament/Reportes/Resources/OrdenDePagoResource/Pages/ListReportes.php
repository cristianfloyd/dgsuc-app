<?php /** @noinspection PhpUnused */

namespace App\Filament\Reportes\Resources\OrdenDePagoResource\Pages;

use Exception;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\RepOrdenPagoService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\MultipleIdLiquiSelector;
use App\Filament\Reportes\Resources\OrdenDePagoResource;

class ListReportes extends ListRecords
{
    protected static string $resource = OrdenDePagoResource::class;
    protected static string $view = 'filament.resources.reporte-resource.pages.list-reportes';

    public bool $reporteGenerado = false;
    protected RepOrdenPagoService $ordenPagoService;

    public static function getEloquentQuery()
    {
        $repOrdenPagoService = app(RepOrdenPagoService::class);
        $repOrdenPagoRecords = $repOrdenPagoService->getAllRepOrdenPago();


        return $repOrdenPagoRecords->toQuery();
    }

    public function boot(RepOrdenPagoService $ordenPagoService): void
    {
        $this->ordenPagoService = $ordenPagoService;
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
            // Action::make('verOP')
            //     ->label('Ver OP')
            //     ->url(route('reporte-orden-pago-pdf'), shouldOpenInNewTab: true)
            //     ->visible(fn() => $this->reporteGenerado),
            // Action::make('generarReporte')
            //     ->label('Generar OP')
            //     //->action(fn() => $this->generarReporte())
            //     ->action(function () {
            //         // metodo que ejecute la función almacenada
            //         if ($this->generarReporte()) {
            //             Notification::make()->title('Reporte generado')->success()->send();
            //         }
            //     }),
            Action::make('generarReporte')
                ->label('Generar Reporte')
                ->icon('heroicon-o-document-currency-dollar')
                ->url('/reportes/orden-de-pagos/crear')
                ->color('success')

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
            DB::connection('pgsql-mapuche')->select('SELECT suc.rep_orden_pago(?)', ['{' . implode(',', $selectedLiquidaciones) . '}']);
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
