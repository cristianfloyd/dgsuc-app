<?php /** @noinspection PhpUnused */

namespace App\Filament\Reportes\Resources\OrdenDePagoResource\Pages;

use Exception;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use App\Services\RepOrdenPagoService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Models\Reportes\RepOrdenPagoModel;
use Filament\Tables\Concerns\HasEmptyState;
use App\Filament\Widgets\MultipleIdLiquiSelector;
use App\Filament\Reportes\Resources\OrdenDePagoResource;
use App\Filament\Reportes\Resources\OrdenDePagoResource\Widgets\OrdenPagoStatsWidget;

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
        // Verificar si existen registros en la tabla
        $this->reporteGenerado = RepOrdenPagoModel::whereNotNull('nro_liqui')->exists();
        parent::mount();
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
            Action::make('verOP')
                ->label('Ver OP')
                ->url(route('reporte-orden-pago-pdf'), shouldOpenInNewTab: true)
                ->visible(fn() => $this->reporteGenerado),
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

    public function getHeaderWidgets(): array
    {
        return [
            OrdenPagoStatsWidget::class,
        ];
    }
}
