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


    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            //ReporteResource::getActions(),
            Action::make('ordenPagoReporte')
                ->label('Ver OP')
                ->modalContent(fn () => view('modals.orden-pago-reporte', ['liquidacionId' => 1]))
                ->modalWidth('7xl'),
            Action::make('generarReporte')
                ->label('Generar OP')
                //->action(fn() => $this->generarReporte())
                ->action(function () {
                    // Aquí se llamaría a un servicio que ejecute la función almacenada
                    if($this->generarReporte())
                    {
                        Notification::make()->title('Reporte generado')->success()->send();
                    }
                })
        ];
    }

    public function generarReporte(): bool
    {
        try {
            $selectedLiquidaciones = $this->getLiquidacionesSeleccionadas();
            DB::connection('pgsql-mapuche')->select('SELECT suc.rep_orden_pago(?)', ['{' . implode(',', $selectedLiquidaciones) . '}']);
            Notification::make()->title('Reporte generado')->success()->send();
            return true;
        } catch (\Exception $e) {
            Log::error('Error al generar el reporte: ' . $e->getMessage());
            Notification::make()->title('Error al generar el reporte')->danger()->send();
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
}
