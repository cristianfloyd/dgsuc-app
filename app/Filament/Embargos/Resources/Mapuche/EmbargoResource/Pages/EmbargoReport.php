<?php

namespace App\Filament\Embargos\Resources\Mapuche\EmbargoResource\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use App\Models\Mapuche\Embargo;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Reportes\EmbargoReportModel;
use App\Services\Reportes\EmbargoReportService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Embargos\Resources\Mapuche\EmbargoResource;

class EmbargoReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    protected static string $resource = EmbargoResource::class;
    protected static ?string $title = 'Reporte de Embargos';
    protected static string $view = 'filament.resources.embargo.pages.report';
    protected static ?string $slug = 'reporte-embargos';


    // #[Reactive]
    public $nro_liqui = null;
    public $reportData;
    public $perPage = 5;
    protected Table $table;


    public function mount(): void
    {
        $this->form->fill();
        $this->table = $this->makeTable();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('nro_liqui')
                ->label('Liquidación')
                ->options(function() {
                    return Dh22::query()->select('nro_liqui')
                        ->distinct()
                        ->orderByDesc('nro_liqui')
                        ->pluck('nro_liqui', 'nro_liqui');
                })
                ->required()
        ];
    }

    // protected function getTableQuery(): Builder
    // {
    //     // Validamos que se haya seleccionado una liquidación
    //     if (!$this->nro_liqui) {
    //         // Retornamos un query vacío para evitar cargar datos
    //         Log::info('No se ha seleccionado una liquidación');

    //         return EmbargoReportModel::query()->whereRaw('1 = 0');
    //     }


    //     try{
    //         // Generamos y establecemos los datos del reporte
    //         $reportData = $this->generateReport();
    //         Log::info('reportData ',$reportData->toArray());


    //         // Utilizamos el nuevo método setReportData
    //         EmbargoReportModel::setReportData($reportData->toArray());
    //         Log::info('EmbargoReportModel ',EmbargoReportModel::$reportData);

    //         // Retornamos el query builder del modelo
    //         $query = EmbargoReportModel::query();
    //         Log::debug('Contenido de $query->get():', ['data' => $query->get()]);
    //         return $query;

    //     } catch (\Exception $e) {
    //         // Manejo de excepciones y registro de errores
    //         Log::error('Error al generar el reporte de embargos', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         // Retornamos un query vacío en caso de error
    //         return EmbargoReportModel::query()->whereRaw('1 = 0');
    //     }
    // }



    /**
     * Define la configuración de la tabla.
     */
    protected function table(Table $table): Table
    {
        return $table
            ->query(EmbargoReportModel::query())
            ->columns([
                TextColumn::make('nro_legaj')->label('Legajo')->sortable(),
                TextColumn::make('nro_cargo')->label('Cargo')->sortable(),
                TextColumn::make('nombre_completo')->label('Nombre')->searchable(),
                TextColumn::make('codc_uacad')->label('Unidad Acad'),
                TextColumn::make('caratula')->label('Caratula')->limit(30),
                TextColumn::make('nro_embargo')->label('Nro. Embargo'),
                TextColumn::make('codn_conce')->label('Concepto'),
                TextColumn::make('importe_descontado')->label('Importe')->money('ARS')
            ])
            ->defaultSort('nro_legaj', 'asc');
    }

    public function updatedNroLiqui()
    {
        Log::info("nro_lqui actualizado: ",[$this->nro_liqui]);
    }

    public function updatedReportData()
    {
        Log::info("reportData actualizado: ", [$this->reportData]);
    }

    public function generateReport()
    {
        if (!$this->nro_liqui) {
            return;
        }

        try {

            $reportService = app(EmbargoReportService::class);
            $reportData = $reportService->generateReport($this->nro_liqui);
            EmbargoReportModel::setReportData($reportData->toArray());
            $this->refreshTable();

        } catch (\Exception $e) {
            Log::error('Error al generar reporte', ['error' => $e->getMessage()]);
            $this->notify('error', 'Error al generar el reporte');
        }
    }

    public function refreshTable()
    {
        $this->dispatch('refresh');
    }
}
