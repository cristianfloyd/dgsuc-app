<?php

namespace App\Filament\Reportes\Resources\OrdenDePagoResource\Pages;

use Exception;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\RepOrdenPagoService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use App\Models\Reportes\RepOrdenPagoModel;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Widgets\MultipleIdLiquiSelector;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Filament\Reportes\Resources\OrdenDePagoResource;

class ReporteOrdenPago extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = OrdenDePagoResource::class;
    protected static ?string $title = 'Crear Reporte de Ordenes de Pago';
    protected static string $view = 'filament.resources.orden-de-pago-resource.pages.reporte-orden-pago';
    protected static ?string $slug = 'crear';
    protected Table $table;

    public bool $reporteGenerado = false;
    protected RepOrdenPagoService $ordenPagoService;

    public function mount(RepOrdenPagoService $ordenPagoService)
    {
        $this->ordenPagoService = $ordenPagoService;
        $this->ordenPagoService->ensureTableAndFunction();
        // Limpiar la session anterior
        session()->forget('idsLiquiSelected');
        // Inicializar estado
        $this->reporteGenerado = false;

        Log::info('ReporteOrdenPago: Se ha inicializado la página');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('nro_liqui')->label('Nro. Liquidación'),
                TextColumn::make('banco')->label('Banco'),
                TextColumn::make('codn_funci')->label('Función'),
                TextColumn::make('codn_fuent')->label('Fuente'),
                TextColumn::make('codc_uacad')->label('Unidad Académica'),
                TextColumn::make('caracter')->label('Carácter'),
                TextColumn::make('codn_progr')->label('Programa'),
                TextColumn::make('remunerativo')->money('ARS')->label('Remunerativo')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('no_remunerativo')->money('ARS')->label('No Remunerativo')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('bruto')->money('ARS')->label('bruto'),
                TextColumn::make('descuentos')->money('ARS'),
                TextColumn::make('aportes')->money('ARS'),
                TextColumn::make('estipendio')->money('ARS'),
            ])
            ->defaultPaginationPageOption(5)
            ->striped()
            ->emptyStateHeading('No se encontraron registros')
            ->emptyStateDescription('Genera un reporte de ordenes de pago para ver los resultados')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->defaultSort('nro_liqui', 'desc');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verOP')
                ->label('Ver OP')
                ->url(route('reporte-orden-pago-pdf'), shouldOpenInNewTab: true)
                ->visible(fn() => $this->reporteGenerado),
            Action::make('generarReporte')
                ->label('Generar OP')
                ->action(function () {
                    if ($this->generarReporte()) {
                        Notification::make()->title('Reporte generado')->success()->send();
                    }
                })
        ];
    }

    #[On('idsLiquiSelected')]
    public function actualizarLiquidacionesSeleccionadas($liquidaciones): void
    {
        session(['idsLiquiSelected' => $liquidaciones]);
        Log::debug("Liquidaciones seleccionadas guardadas en sesión", ['liquidaciones' => $liquidaciones]);
    }

    public function generarReporte(): bool
    {
        $selectedLiquidaciones = session('idsLiquiSelected', []);

        if (empty($selectedLiquidaciones)) {
            Notification::make()
                ->title('Seleccione al menos una liquidación')
                ->warning()
                ->send();
            return false;
        }

        try {
            app(RepOrdenPagoService::class)->generateReport($selectedLiquidaciones);
            // $this->ordenPagoService->generateReport($selectedLiquidaciones);
            $this->reporteGenerado = true;
            return true;
        } catch (Exception $e) {
            Log::error('Error al generar el reporte: ' . $e->getMessage());
            Notification::make()
                ->title('Error al generar el reporte')
                ->danger()
                ->send();
            return false;
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MultipleIdLiquiSelector::class,
        ];
    }

    protected function getTableQuery(): Builder
    {
        return RepOrdenPagoModel::query();
    }
}

