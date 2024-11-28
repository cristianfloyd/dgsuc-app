<?php

namespace App\Filament\Embargos\Resources\Mapuche\EmbargoResource\Pages;

use App\Models\Mapuche\Dh22;
use App\Models\Mapuche\Embargo;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use App\Services\Reportes\EmbargoReportService;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Filament\Embargos\Resources\Mapuche\EmbargoResource;

class EmbargoReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    protected static string $resource = EmbargoResource::class;
    protected static ?string $title = 'Reporte de Embargos';
    protected static string $view = 'filament.resources.embargo.pages.report';

    public $nro_liqui;
    public $reportData;
    public $perPage = 25;

    public function mount(): void
    {
        $this->form->fill();
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

    protected function getTableQuery(): Builder
    {
        return Embargo::query()
            ->with(['datosPersonales', 'tipoEmbargo'])
            ->whereHas('estado', fn($query) => $query->where('id_estado_embargo', 2));
    }

    public function getTableRecords(): Collection | Paginator
    {
        if (!$this->reportData) {
            return collect();
        }

        $data = collect($this->reportData)->flatten(1);

        return new LengthAwarePaginator(
            $data->forPage($this->getPage(), $this->perPage),
            $data->count(),
            $this->perPage,
            $this->getPage()
        );
    }

    // protected function getTableColumns(): array
    // {
    //     return [
    //         TextColumn::make('nro_legaj'),
    //         TextColumn::make('nro_cargo'),
    //         TextColumn::make('datosPersonales.nombre_completo')
    //             ->label('Nombre')
    //             ->limit(30),
    //         TextColumn::make('codc_uacad'),
    //         TextColumn::make('nro_embargo'),
    //         TextColumn::make('codn_conce'),
    //         TextColumn::make('importe_descontado')
    //             ->money('ARS'),
    //     ];
    // }

    public function generateReport(): void
    {
        $reportService = app(EmbargoReportService::class);
        $this->reportData = $reportService->generateReport($this->nro_liqui);
    }
}
