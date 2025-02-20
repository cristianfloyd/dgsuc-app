<?php

namespace App\Filament\Afip\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use App\Exports\SicossReporteExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use App\Services\Reports\SicossReporteService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Mapuche\MapucheSicossReporte;
use Filament\Support\Enums\Alignment;
use App\Filament\Afip\Pages\Widgets\SicossTotalesWidget;

class SicossReportePage extends Page implements \Filament\Tables\Contracts\HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Reporte SICOSS';
    protected static ?string $title = 'Reporte SICOSS';
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?int $navigationSort = 1;

    public $periodoFiscal;
    public $anio;
    public $mes;
    protected $queryString = ['periodoFiscal'];

    protected static string $view = 'filament.pages.sicoss-reporte';

    protected SicossReporteService $sicossReporteService;

    public function boot()
    {
        $this->sicossReporteService = app(SicossReporteService::class);
    }

    public function mount(): void
    {
        $periodoActual = $this->sicossReporteService->getPeriodosFiscales();
        $firstKey = array_key_first($periodoActual);
        $this->periodoFiscal = request()->query('periodoFiscal', $firstKey);
        $this->anio = substr($this->periodoFiscal, 0, 4);
        $this->mes = substr($this->periodoFiscal, 4, 2);
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('periodoFiscal')
                            ->label('Período Fiscal')
                            ->options($this->sicossReporteService->getPeriodosFiscales())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                if (!$state) return;

                                $this->anio = substr($state, 0, 4);
                                $this->mes = substr($state, 4, 2);
                                $this->table->query(fn() => MapucheSicossReporte::query()->getReporte($this->anio, $this->mes));
                            }),
                    ])
                    ->columns(1)
                    ->collapsed(false),
            ]);
    }

    public function getTotales()
    {
        if (!$this->anio || !$this->mes) {
            return [];
        }
        return $this->sicossReporteService->getTotales($this->anio, $this->mes)->toArray();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SicossTotalesWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            SicossTotalesWidget::class => [
                'totales' => $this->getTotales(),
            ],
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MapucheSicossReporte::query()->getReporte($this->anio, $this->mes))
            ->columns([
                TextColumn::make('nro_liqui')
                    ->label('N° Liq')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
                TextColumn::make('desc_liqui')
                    ->label('Descripción')
                    ->alignment(Alignment::Left)
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
                TextColumn::make('remunerativo')
                    ->label('Remunerativo')
                    ->money('ARS')
                    ->alignment(Alignment::End)
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
                TextColumn::make('no_remunerativo')
                    ->label('No Remunerativo')
                    ->money('ARS')
                    ->alignment(Alignment::End)
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
                TextColumn::make('aportesijpdh21')
                    ->label('Aportes SIJP')
                    ->money('ARS')
                    ->alignment(Alignment::End)
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
                TextColumn::make('aporteinssjpdh21')
                    ->label('Aportes INSSJP')
                    ->money('ARS')
                    ->alignment(Alignment::End)
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
                TextColumn::make('contribucionsijpdh21')
                    ->label('Contribución SIJP')
                    ->money('ARS')
                    ->alignment(Alignment::End)
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
                TextColumn::make('contribucioninssjpdh21')
                    ->label('Contribución INSSJP')
                    ->money('ARS')
                    ->alignment(Alignment::End)
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
            ])
            ->defaultSort('nro_liqui')
            ->filters([
                // Filter::make('con_aportes')
                //     ->label('Con Aportes')
                //     ->query(fn($query) => $query->whereRaw('(aportesijpdh21 + aporteinssjpdh21) > 0')),
                // Filter::make('con_contribuciones')
                //     ->label('Con Contribuciones')
                //     ->query(fn($query) => $query->whereRaw('(contribucionsijpdh21 + contribucioninssjpdh21) > 0')),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar Todo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        return Excel::download(
                            new SicossReporteExport(
                                $this->anio,
                                $this->mes,
                                null,
                                $this->getTotales()
                            ),
                            "reporte_sicoss_{$this->anio}_{$this->mes}.xlsx"
                        );
                    }),
            ])
            ->bulkActions([
                BulkAction::make('export')
                    ->label('Exportar Seleccionados')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($records) {
                        return Excel::download(
                            new SicossReporteExport(
                                $this->anio,
                                $this->mes,
                                $records,
                                $this->getTotales()
                            ),
                            "reporte_sicoss_{$this->anio}_{$this->mes}_seleccionados.xlsx"
                        );
                    })
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
