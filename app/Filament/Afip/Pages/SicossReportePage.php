<?php

namespace App\Filament\Afip\Pages;

use App\Exports\SicossReporteExport;
use App\Filament\Afip\Pages\Widgets\SicossTotalesWidget;
use App\Models\Mapuche\MapucheSicossReporte;
use App\Services\Reports\SicossReporteService;
use BackedEnum;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use Override;
use UnitEnum;

class SicossReportePage extends Page implements HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $periodoFiscal;

    public $anio;

    public $mes;

    public bool $isLoading = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Reporte SICOSS';

    protected static ?string $title = 'Reporte SICOSS';

    protected static string|UnitEnum|null $navigationGroup = 'SICOSS';

    protected static ?int $navigationSort = 2;

    protected $queryString = ['periodoFiscal'];

    protected string $view = 'filament.pages.sicoss-reporte';

    protected SicossReporteService $sicossReporteService;

    public function boot(): void
    {
        $this->sicossReporteService = resolve(SicossReporteService::class);
    }

    public function mount(): void
    {
        $periodoActual = $this->sicossReporteService->getPeriodosFiscales();
        $firstKey = array_key_first($periodoActual);
        $this->periodoFiscal = request()->query('periodoFiscal', $firstKey);
        $this->anio = substr($this->periodoFiscal, 0, 4);
        $this->mes = substr($this->periodoFiscal, 4, 2);

        Notification::make()->title('Cargando datos del reporte')->body('Este proceso puede tomar unos segundos...')->info()->send();

        $this->form->fill();

        // Actualizar los totales después de montar el componente
        $this->updateWidgetData();
    }

    public function updateWidgetData(): void
    {
        try {
            $totales = $this->getTotales();
            $this->dispatch('totales-actualizados', totales: $totales);
        } catch (Exception $e) {
            Notification::make()->title('Error al actualizar totales')->danger()->send();

            logger()->error('Error actualizando totales:', [
                'error' => $e->getMessage(),
                'anio' => $this->anio,
                'mes' => $this->mes,
            ]);
        }
    }

    public function getColumnSpan(): string
    {
        return 'full'; // O el valor que necesites para el tamaño de la columna
    }

    public function getColumnStart(): string
    {
        return 'xl';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Select::make('periodoFiscal')
                        ->label('Período Fiscal')
                        ->options($this->sicossReporteService->getPeriodosFiscales())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->hint(
                            new HtmlString(
                                Blade::render(
                                    '<div wire:loading wire:target="periodoFiscal">
                                            <x-filament::loading-indicator class="h-5 w-5" />
                                            </div>',
                                ),
                            ),
                        )
                        ->afterStateUpdated(function ($state): void {
                            if (!$state) {
                                return;
                            }

                            $this->isLoading = true;

                            // Despachar un evento para actualizar la UI inmediatamente
                            $this->dispatch('loading-started');

                            $this->anio = substr($state, 0, 4);
                            $this->mes = substr($state, 4, 2);

                            // Usar dispatch para ejecutar la actualización de datos
                            $this->dispatch('update-data');
                        }),
                ])
                ->columns(1),
        ]);
    }

    #[On('update-data')]
    public function updateData(): void
    {
        try {
            // Actualizar la tabla
            $this->table->query(fn() => MapucheSicossReporte::query()->getReporte($this->anio, $this->mes));

            // Actualizar los totales
            $this->updateWidgetData();

            Notification::make()->title('Datos actualizados')->success()->send();
        } catch (Exception $e) {
            Notification::make()->title('Error al cargar los datos')->danger()->send();

            logger()->error('Error cargando datos:', [
                'error' => $e->getMessage(),
                'anio' => $this->anio,
                'mes' => $this->mes,
            ]);
        } finally {
            $this->isLoading = false;
            $this->dispatch('loading-finished');
        }
    }

    /**
     * Obtiene los totales del reporte SICOSS para un año y mes específicos.
     *
     * Recupera los totales del reporte utilizando el servicio SicossReporteService.
     * Si no se proporciona el año o el mes, registra un mensaje informativo y devuelve un array vacío.
     *
     * @return array Los totales del reporte SICOSS, o un array vacío si faltan datos.
     */
    public function getTotales(): array
    {
        if (!$this->anio || !$this->mes) {
            Log::info('SicossReportePage:: No se pudo obtener los totales', ['anio' => $this->anio, 'mes' => $this->mes]);

            return [];
        }

        return $this->sicossReporteService->getTotales($this->anio, $this->mes)->toArray();
    }

    #[Override]
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
                TextColumn::make('nro_liqui')->label('N° Liq')->sortable()->alignment(Alignment::Center)->size(TextSize::ExtraSmall),
                TextColumn::make('desc_liqui')->label('Descripción')->alignment(Alignment::Left)->size(TextSize::ExtraSmall),
                TextColumn::make('c305')->label('305')->sortable()->alignment(Alignment::Center)->size(TextSize::ExtraSmall),
                TextColumn::make('c306')->label('306')->sortable()->alignment(Alignment::Center)->size(TextSize::ExtraSmall),
                TextColumn::make('remunerativo')->label('Remunerativo')->money('ARS')->alignment(Alignment::End)->size(TextSize::ExtraSmall),
                TextColumn::make('no_remunerativo')->label('No Remunerativo')->money('ARS')->alignment(Alignment::End)->size(TextSize::ExtraSmall),
                TextColumn::make('aportesijpdh21')->label('Aportes SIJP')->money('ARS')->alignment(Alignment::End)->size(TextSize::ExtraSmall),
                TextColumn::make('aporteinssjpdh21')->label('Aportes INSSJP')->money('ARS')->alignment(Alignment::End)->size(TextSize::ExtraSmall),
                TextColumn::make('contribucionsijpdh21')->label('Contribución SIJP')->money('ARS')->alignment(Alignment::End)->size(TextSize::ExtraSmall),
                TextColumn::make('contribucioninssjpdh21')->label('Contribución INSSJP')->money('ARS')->alignment(Alignment::End)->size(TextSize::ExtraSmall),
            ])
            ->deferLoading()
            ->defaultSort('nro_liqui')
            ->filters(
                [

                ],
                layout: FiltersLayout::AboveContent,
            )
            ->filtersFormColumns(3)
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('180s')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar Todo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn() => Excel::download(new SicossReporteExport($this->anio, $this->mes, null, $this->getTotales()), "reporte_sicoss_{$this->anio}_{$this->mes}.xlsx")),
            ])
            ->toolbarActions([
                BulkAction::make('export')
                    ->label('Exportar Seleccionados')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn($records) => Excel::download(
                        new SicossReporteExport($this->anio, $this->mes, $records, $this->getTotales()),
                        "reporte_sicoss_{$this->anio}_{$this->mes}_seleccionados.xlsx",
                    )),
            ])
            ->persistFiltersInSession()
            ->persistSortInSession();
    }

    /**
     * Método para refrescar los datos cuando sea necesario.
     */
    public function refreshData(): void
    {
        $this->sicossReporteService->invalidateCache($this->anio, $this->mes);
        $this->updateWidgetData();

        Notification::make()->title('Datos actualizados')->success()->send();
    }

    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [SicossTotalesWidget::class];
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [];
    }
}
