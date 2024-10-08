<?php

namespace App\Filament\Resources\ReporteResource\Pages;

use Livewire\Attributes\On;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use App\Filament\Widgets\IdLiquiSelector;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ReporteResource;
use App\Filament\Widgets\MultipleIdLiquiSelector;

class ListReportes extends ListRecords
{
    protected static string $resource = ReporteResource::class;
     protected static string $view = 'filament.resources.reporte-resource.pages.list-reportes';

    public $reporteGenerado = false;

    public function boot()
    {
        Log::info('ListReportes booted', ['session' => session()->all()]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            //ReporteResource::getActions(),
            Action::make('verOP')
                ->label('Ver OP')
                ->url(route('reporte-orden-pago-pdf'), shouldOpenInNewTab: true)
                ->visible(fn () => $this->reporteGenerado),
            Action::make('generarReporte')
                ->label('Generar OP')
                //->action(fn() => $this->generarReporte())
                ->action(function () {
                    // metodo que ejecute la función almacenada
                    if($this->generarReporte())
                    {
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

    #[On('idsLiquiSelected')]
    public function actualizarLiquidacionesSeleccionadas($liquidaciones)
    {
        session(['idsLiquiSelected' => $liquidaciones]);
        Log::debug("se graba en la session --> isdLiquiSelected", ['state' => $liquidaciones]);
    }

    /**
     * Set the value of reporteGenerado
     *
     * @return  self
     */
    public function setReporteGenerado($reporteGenerado)
    {
        $this->reporteGenerado = $reporteGenerado;
        return $this;
    }

    public static function getEloquentQuery()
    {
        return parent::getEloquentQuery()
            ->with('unidadAcademica')
            ->orderBy('nro_liqui', 'asc');
    }

}
