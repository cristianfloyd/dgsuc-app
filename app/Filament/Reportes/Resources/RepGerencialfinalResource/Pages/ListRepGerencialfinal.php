<?php

namespace App\Filament\Reportes\Resources\RepGerencialFinalResource\Pages;

use Exception;
use Filament\Actions;
use Livewire\Attributes\On;
use App\Models\Mapuche\Dh22;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use App\Models\Mapuche\Catalogo\Dh30;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Widgets\IdLiquiSelector;
use Filament\Resources\Pages\ListRecords;
use App\Models\Reportes\RepGerencialFinal;
use App\Services\RepGerencialFinalService;
use App\Filament\Widgets\MultipleIdLiquiSelector;
use App\Filament\Reportes\Resources\RepGerencialFinalResource;
use App\Filament\Reportes\Resources\RepGerencialFinalResource\Widgets\RepGerencialFinalStats;

class ListRepGerencialFinal extends ListRecords
{
    protected static string $resource = RepGerencialfinalResource::class;
    public ?array $idLiquiSelected = [];
    public ?bool $reporteGenerado = false;
    public array $reportFilters = [];
    protected RepGerencialFinalService $repGerencialFinalService;

    public function boot(RepGerencialFinalService $repGerencialFinalService)
    {
        $this->repGerencialFinalService = $repGerencialFinalService;
        Log::info('ListRepGerencialfinal: Se ha inicializado la página');
    }

    public function mount(): void
    {
        $this->checkAndCreateBaseStructure();

        Log::info('ListRepGerencialfinal: Se ha montado la página');
    }

    protected function checkAndCreateBaseStructure(): void
    {
        try {
            if (!Schema::connection($this->repGerencialFinalService->getConnectionName())->hasTable('suc.rep_ger_final')) {
                $this->repGerencialFinalService->createTable();

                Notification::make()
                    ->title('Estructura base creada exitosamente')
                    ->success()
                    ->send();
            }
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al crear la estructura base')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generar Reporte')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    // Selector de Liquidaciones
                    Select::make('liquidaciones')
                    ->label('Liquidaciones')
                    ->multiple()
                    ->required()
                    ->options(fn() => Dh22::getLiquidacionesForWidget()
                        ->pluck('desc_liqui', 'nro_liqui'))
                    ->searchable()
                    ->preload()
                    ->live(),

                    // Filtro regional
                    Select::make('codc_regio')
                        ->label('Regional')
                        ->options(fn() => Dh30::where('nro_tabla', 2)
                            ->pluck('desc_item', 'desc_abrev'))
                        ->searchable(),

                    // Filtro unidad académica
                    Select::make('codc_uacad')
                        ->label('Unidad Académica')
                        ->options(fn() => Dh30::where('nro_tabla', 13)
                            ->pluck('desc_item', 'desc_abrev'))
                        ->searchable(),

                    // Filtro escalafón
                    Select::make('codigoescalafon')
                        ->label('Escalafón')
                        ->options([
                            'D' => 'Docente',
                            'N' => 'Nodocente',
                            'C' => 'Contratado'
                        ]),

                    // Filtro carácter
                    Select::make('codc_carac')
                        ->label('Carácter')
                        ->options(fn() => Dh30::where('nro_tabla', 3)
                            ->pluck('desc_item',  'desc_abrev'))
                        ->searchable(),

                    // Filtro legajo
                    TextInput::make('nro_legaj')
                        ->label('Legajo')
                        ->numeric(),

                    // Filtro fuente financiamiento
                    Select::make('codn_fuent')
                        ->label('Fuente Financiamiento')
                        ->options(fn() => Dh30::where('nro_tabla', 4)
                            ->pluck('desc_item', 'desc_abrev'))
                        ->searchable(),
                ])
                ->action(function(array $data) {
                    $this->reportFilters = $data;
                    $this->idLiquiSelected = $data['liquidaciones'];
                    $this->generateReport();
                }),
            Action::make('clear')
                ->label('Limpiar Datos')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function() {
                    try {
                        $this->repGerencialFinalService->dropPreviousTables();
                        RepGerencialFinal::truncate();

                        Notification::make()
                            ->title('Datos limpiados exitosamente')
                            ->success()
                            ->send();

                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Error al limpiar los datos')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RepGerencialFinalStats::class,
        ];
    }

    #[On('idsLiquiSelected')]
    public function actualizarLiquidacionesSeleccionadas($liquidaciones): void
    {
        session(['idsLiquiSelected' => $liquidaciones]);
        $this->idLiquiSelected = $liquidaciones;
        Log::info('Liquidaciones seleccionadas: ' . implode(', ', $this->idLiquiSelected));
    }

    private function generarReporte(): bool
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
            $this->repGerencialFinalService->processReport($selectedLiquidaciones);
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

    protected function generateReport(): void
    {
        try {
            $selectedLiquidaciones = $this->idLiquiSelected;


            if (empty($selectedLiquidaciones)) {
                Notification::make()
                    ->title('Seleccione al menos una liquidación')
                    ->warning()
                    ->send();
                return;
            }

            $this->repGerencialFinalService->processReport(
                $selectedLiquidaciones,
                $this->reportFilters
            );

            Notification::make()
                ->title('Reporte generado exitosamente')
                ->success()
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title('Error al generar el reporte')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
