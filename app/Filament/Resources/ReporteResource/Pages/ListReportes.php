<?php

namespace App\Filament\Resources\ReporteResource\Pages;

use App\Filament\Resources\ReporteResource;
use App\Filament\Widgets\MultipleIdLiquiSelector;
use App\Services\RepOrdenPagoService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ListReportes extends ListRecords
{
    protected static string $resource = ReporteResource::class;
    protected static string $view = 'filament.resources.reporte-resource.pages.list-reportes';

    public bool $reporteGenerado = false;
    protected RepOrdenPagoService $ordenPagoService;

    public static function getEloquentQuery(): Builder
    {
//        return parent::getEloquentQuery()
//            ->with('unidadAcademica')
//            ->orderBy('nro_liqui', 'asc');
        // Usar el servicio RepOrdenPagoService para obtener los datos
        $repOrdenPagoService = app(RepOrdenPagoService::class);
        $repOrdenPagoRecords = $repOrdenPagoService->getAllRepOrdenPago();

        /**
         * Devuelve una instancia de generador de consultas para los registros RepOrdenPago.
         *
         * @return Builder
         */
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
            // Actions\CreateAction::make(),
            //ReporteResource::getActions(),
            Action::make('verOP')
                ->label('Ver OP')
                ->url(route('reporte-orden-pago-pdf'), shouldOpenInNewTab: true)
                ->visible(fn() => $this->reporteGenerado),
            Action::make('generarReporte')
                ->label('Generar OP')
                //->action(fn() => $this->generarReporte())
                ->action(function () {
                    // metodo que ejecute la función almacenada
                    if ($this->generarReporte()) {
                        Notification::make()->title('Reporte generado')->success()->send();
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
            DB::connection('pgsql-mapuche')->select('SELECT suc.rep_orden_pago(?)', ['{' . implode(',', $selectedLiquidaciones) . '}']);
            Notification::make()->title('Reporte generado')->success()->send();
            $this->reporteGenerado = true;
            return true;
        } catch (\Exception $e) {
            Log::error('Error al generar el reporte: ' . $e->getMessage());
            Notification::make()->title('Error al generar el reporte')->danger()->send();
            $this->reporteGenerado = false;
            return false;
        }
    }

    public function getLiquidacionesSeleccionadas()
    {
        $data = session('idsLiquiSelected', []);
        Log::debug("getLiquidacionesSeleccionadas", ['data' => $data]);
        return $data;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MultipleIdLiquiSelector::class,
        ];
    }

}
