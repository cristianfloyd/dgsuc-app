<?php

namespace App\Filament\Embargos\Resources\Mapuche\EmbargoResource\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Illuminate\Support\Facades\Log;
use App\Exports\EmbargoReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Reportes\EmbargoReportModel;
use Filament\Tables\Actions\ExportBulkAction;
use App\Services\Reportes\EmbargoReportService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Embargos\Resources\Mapuche\EmbargoResource;
use App\Filament\Exports\Reportes\EmbargoReportModelExporter;

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
        EmbargoReportModel::createTableIfNotExists();

        $this->nro_liqui = session()->get('selected_nro_liqui');
        $this->form->fill([
            'nro_liqui' => $this->nro_liqui
        ]);
        $this->table = $this->makeTable();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('nro_liqui')
                ->label('Liquidación')
                ->options(function() {
                    return Dh22::query()->select('nro_liqui', 'desc_liqui')
                        ->distinct()
                        ->orderByDesc('nro_liqui')
                        ->pluck('desc_liqui', 'nro_liqui');
                })
                ->required()
        ];
    }

    protected function getTableQuery(): Builder
    {
        // Obtenemos el ID de la sesión actual
        $sessionId = session()->getId();

        // Retornamos el query del modelo filtrado por sesión
        return EmbargoReportModel::query()->where('session_id', $sessionId);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $query = EmbargoReportModel::query()
                    ->select([
                        'nro_legaj',
                        'nro_cargo',
                        'nombre_completo',
                        'codc_uacad',
                        'caratula',
                        'nro_embargo',
                        'codn_conce',
                        'importe_descontado'
                    ])
                    ->where('session_id', session()->getId());

                    return Excel::download(
                        new EmbargoReportExport($query),
                        'embargos-' . now()->format('Y-m-d') . '.xlsx'
                    );
                }),
            ExportAction::make('export')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->exporter(EmbargoReportModelExporter::class)
        ];
    }

    private function getExportAction()
    {
        return \Filament\Tables\Actions\Action::make('export')
            ->label('Exportar')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(function () {
                $this->exportToExcel();
            });
    }

    /**
     * Define la configuración de la tabla.
     */
    protected function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('id')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nro_legaj')->label('Legajo')->sortable()->searchable()->numeric(),
                TextColumn::make('nro_cargo')->label('Cargo')->sortable()->numeric(),
                TextColumn::make('nombre_completo')->label('Nombre')->searchable(),
                TextColumn::make('codc_uacad')->label('Unidad Acad'),
                TextColumn::make('caratula')->label('Caratula')->limit(15)
                    ->tooltip(fn(TextColumn $column): string => $column->getState()),
                TextColumn::make('nro_embargo')->label('Nro. Embargo')->numeric(),
                TextColumn::make('codn_conce')->label('Concepto')->numeric(),
                TextColumn::make('importe_descontado')->label('Importe')->money('ARS'),
                TextColumn::make('nro_liqui')->label('nro_lqui')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nro_legaj', 'asc')
            ->bulkActions([
                ExportBulkAction::make('export')
                ->label('Exportar')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->exporter(EmbargoReportModelExporter::class),
            ]);
    }

    public function updatedNroLiqui()
    {
        Log::info("nro_lqui actualizado: ",[$this->nro_liqui]);


        // Guardamos el nro_liqui en la sesión
        session()->put('selected_nro_liqui', $this->nro_liqui);

        // Verificamos si ya existen datos para esta liquidación
        if (!$this->hasReportData()) {
            // Solo generamos el reporte si no existe
            $this->generateReport();
        }
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

        // Verificamos si ya hay datos para la sesión actual
        if ($this->hasReportData()) {
            Notification::make('info')
                ->title('Ya hay datos para esta sesión.')
                ->send();
            return;
        }

        try {

            $reportService = app(EmbargoReportService::class);
            $reportData = $reportService->generateReport($this->nro_liqui);
            EmbargoReportModel::setReportData($reportData);
            $this->refreshTable();
            Notification::make('success')
                ->title( 'Reporte generado correctamente.')
                ->send();
        } catch (\Exception $e) {
            Log::error('Error al generar reporte', ['error' => $e->getMessage()]);
            Notification::make('error')
                ->title('Error al generar reporte')
                ->send();
        }
    }

    public function refreshTable()
    {
        $this->dispatch('refresh');
    }

    protected function hasReportData(): bool
    {
        $sessionId = session()->getId();
        return EmbargoReportModel::where('session_id', $sessionId)
            ->where('nro_liqui', $this->nro_liqui)
            ->exists();
    }
}
