<?php

namespace App\Filament\Afip\Pages;

use App\Enums\ConceptosSicossEnum;
use App\Exports\Sicoss\AportesDiferenciasExport;
use App\Exports\Sicoss\ConceptosExport;
use App\Exports\Sicoss\ContribucionesDiferenciasExport;
use App\Exports\Sicoss\CuilsDiferenciasExport;
use App\Exports\Sicoss\ResumenDependenciasExport;
use App\Filament\Afip\Actions\EjecutarControlAportesAction;
use App\Filament\Afip\Actions\EjecutarControlConceptosAction;
use App\Filament\Afip\Actions\EjecutarControlContribucionesAction;
use App\Filament\Afip\Actions\EjecutarControlCuilsAction;
use App\Filament\Afip\Pages\Traits\HasSicossControlTables;
use App\Models\ControlAportesDiferencia;
use App\Models\ControlConceptosPeriodo;
use App\Models\ControlContribucionesDiferencia;
use App\Models\ControlCuilsDiferencia;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\SicossControlService;
use App\Traits\MapucheConnectionTrait;
use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View as ViewFacade;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;

class SicossControles extends Page implements HasTable
{
    use InteractsWithTable {
        InteractsWithTable::getTable insteadof MapucheConnectionTrait;
    }

    // use SicossConnectionTrait;
    use HasSicossControlTables;
    use MapucheConnectionTrait {
        MapucheConnectionTrait::getTable as getMapucheTable;
    }

    public $selectedConnection;

    public $availableConnections = [];

    public $activeTab = 'resumen';

    public $resultadosControles;

    public $cachedStats;

    public $loading = false;

    public $record;

    public $search = '';

    public $year;

    public $month;

    public $conceptosSeleccionados = [];

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'SICOSS';

    protected static string $view = 'filament.afip.pages.sicoss-controles';

    protected static ?string $title = 'Controles SICOSS';

    protected static ?string $slug = 'afip/sicoss-controles';

    protected static ?string $panel = 'afip';

    protected PeriodoFiscalService $periodoFiscalService;

    public function boot(PeriodoFiscalService $periodoFiscalService): void
    {
        $this->periodoFiscalService = $periodoFiscalService;
    }

    public function mount(): void
    {
        $this->availableConnections = $this->conexionesDisponibles();

        $this->selectedConnection ??= $this->getConnectionName();

        // Obtener el período fiscal actual
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscalFromDatabase();
        $this->year = $periodoFiscal['year'];
        $this->month = $periodoFiscal['month'];

        $this->conceptosSeleccionados = array_merge(
            ConceptosSicossEnum::getAllAportesCodes(),
            ConceptosSicossEnum::getAllContribucionesCodes(),
            ConceptosSicossEnum::getContribucionesArtCodes(),
        );
    }

    #[On('fiscalPeriodUpdated')]
    public function handlePeriodoFiscalUpdated(): void
    {
        // Obtener el período fiscal de la sesión en lugar de la base de datos
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
        $this->year = $periodoFiscal['year'];
        $this->month = $periodoFiscal['month'];

        // Limpiar caché y resultados anteriores
        $this->resultadosControles = null;
        $this->cachedStats = null;
        cache()->forget('sicoss_resumen_stats');

        // Notificar al usuario
        Notification::make()
            ->title('Período fiscal actualizado')
            ->body("Período actual: {$this->year}-{$this->month}")
            ->success()
            ->send();
    }

    public function ejecutarControles(): void
    {
        Log::info('Iniciando controles SICOSS', ['year' => $this->year, 'month' => $this->month]);
        try {
            $this->loading = true;

            Notification::make()
                ->title('Iniciando controles SICOSS')
                ->info()
                ->send();

            $service = app(SicossControlService::class);
            $service->setConnection($this->getConnectionName());

            // Pasar el período fiscal al servicio
            $this->resultadosControles = $service->ejecutarControlesPostImportacion($this->year, $this->month);

            $diferenciasAportes = abs($this->resultadosControles['totales']['dh21']['aportes'] -
                $this->resultadosControles['totales']['sicoss']['aportes']);

            $diferenciasContribuciones = abs($this->resultadosControles['totales']['dh21']['contribuciones'] -
                $this->resultadosControles['totales']['sicoss']['contribuciones']);

            $viewContent = ViewFacade::make('filament.afip.notifications.control-sicoss', [
                'diferenciasAportes' => number_format($diferenciasAportes, 2, ',', '.'),
                'diferenciasContribuciones' => number_format($diferenciasContribuciones, 2, ',', '.'),
                'totalCuils' => $this->getDiferenciasCount(),
                'totalDependencias' => $this->getDependenciasCount(),
            ])->render();

            // Invalidamos el caché después de ejecutar los controles
            cache()->forget('sicoss_resumen_stats');

            // Actualizamos los stats después de ejecutar los controles
            $this->cargarResumen();

            Notification::make()
                ->success()
                ->title('Controles completados')
                ->body($viewContent)
                ->actions([
                    NotificationAction::make('ver_resumen')
                        ->label('Ver Resumen')
                        ->color('primary')
                        ->icon('heroicon-o-document-text')
                        ->action(fn () => $this->activeTab = 'resumen'),
                ])
                ->persistent()
                ->send();
        } catch (\Exception $e) {
            logger()->error('Error en ejecución de controles SICOSS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('Error en la ejecución')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Obtiene los datos de conteos almacenados en la sesión.
     */
    public function getConteosData(): ?array
    {
        return session()->get('sicoss_conteos');
    }

    /**
     * Verifica si hay datos de conteos disponibles.
     */
    public function hasConteosData(): bool
    {
        return session()->has('sicoss_conteos');
    }

    /**
     * Calcula la diferencia entre DH21 y SICOSS Cálculos.
     */
    public function getDiferenciaConteos(): int
    {
        $conteos = $this->getConteosData();
        if (!$conteos) {
            return 0;
        }

        return abs($conteos['dh21aporte'] - $conteos['sicoss_calculos']);
    }

    public function formatMoney($value): string
    {
        return '$ ' . number_format($value, 2, ',', '.');
    }

    public function getDiferenciasCount(): int
    {
        return ControlAportesDiferencia::count();
    }

    public function getDependenciasCount(): int
    {
        if (!$this->hasResults()) {
            return 0;
        }
        return \count($this->resultadosControles['aportes_contribuciones']['diferencias_por_dependencia']);
    }

    public function table(Table $table): Table
    {
        // Si estamos en la pestaña resumen, retornamos una tabla vacía
        if ($this->activeTab === 'resumen') {
            return $table->query(ControlAportesDiferencia::query());
        }

        $query = match ($this->activeTab) {
            'diferencias_aportes' => ControlAportesDiferencia::query()
                ->with(['sicossCalculo', 'relacionActiva', 'dh01']),
            'diferencias_contribuciones' => ControlContribucionesDiferencia::query()
                ->with(['sicossCalculo', 'relacionActiva', 'dh01']),
            'diferencias_cuils' => ControlCuilsDiferencia::query(),
            'conceptos' => ControlConceptosPeriodo::query()
                ->where('year', $this->year)
                ->where('month', $this->month),
            default => ControlAportesDiferencia::query()
        };

        // TODO: Agregar el header y una vista que incluya un search
        return $table
            ->query($query)
            ->header(view('filament.afip.pages.partials.sicoss-control-header', [
                'year' => $this->year,
                'month' => $this->month,
            ]))
            ->columns($this->getColumnsForActiveTab())
            ->when(
                $this->activeTab !== 'diferencias_cuils' && $this->activeTab !== 'conceptos',
                fn (Table $table) =>
                $table->defaultSort('diferencia', 'desc'),
            )
            ->when(
                $this->activeTab === 'conceptos',
                fn (Table $table) => $table->defaultSort('codn_conce', 'asc'),
            )
            ->striped()
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25, 50, 100])
            ->searchable()
            ->actions($this->getTableActions());
    }

    public function ejecutarControlAportes(): void
    {
        try {
            $this->loading = true;
            $service = app(SicossControlService::class);
            $service->setConnection($this->getConnectionName());

            $resultados = $service->ejecutarControlAportes();

            // Invalidamos el caché después de ejecutar el control
            cache()->forget('sicoss_resumen_stats');

            // Actualizamos los stats
            $this->cargarResumen();

            Notification::make()
                ->success()
                ->title('Control de Aportes Ejecutado')
                ->body('Se ha completado el control de aportes')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error en el control de aportes')
                ->body($e->getMessage())
                ->send();
        } finally {
            $this->loading = false;
        }
    }

    public function ejecutarControlContribuciones(): void
    {
        try {
            $this->loading = true;
            $service = app(SicossControlService::class);
            $service->setConnection($this->getConnectionName());

            // Obtener el período fiscal actual
            $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
            $this->year = $periodoFiscal['year'];
            $this->month = $periodoFiscal['month'];

            $resultados = $service->ejecutarControlContribuciones($this->year, $this->month);

            // Invalidamos el caché después de ejecutar el control
            cache()->forget('sicoss_resumen_stats');

            // Actualizamos los stats
            $this->cargarResumen();

            Notification::make()
                ->success()
                ->title('Control de Contribuciones Ejecutado')
                ->body('Se ha completado el control de contribuciones')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error en el control de contribuciones')
                ->body($e->getMessage())
                ->send();
        } finally {
            $this->loading = false;
        }
    }

    public function ejecutarControlCuils(): void
    {
        try {
            $this->loading = true;
            $service = app(SicossControlService::class);
            $service->setConnection($this->getConnectionName());

            // Obtener el período fiscal actual
            $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
            $this->year = $periodoFiscal['year'];
            $this->month = $periodoFiscal['month'];

            $resultados = $service->ejecutarControlCuils($this->year, $this->month);

            // Invalidamos el caché después de ejecutar el control
            cache()->forget('sicoss_resumen_stats');

            // Actualizamos los stats
            $this->cargarResumen();

            Notification::make()
                ->success()
                ->title('Control de CUILs Ejecutado')
                ->body('Se ha completado el control de CUILs')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error en el control de CUILs')
                ->body($e->getMessage())
                ->send();
        } finally {
            $this->loading = false;
        }
    }

    public function ejecutarControlConteos(): void
    {
        try {
            $this->loading = true;

            // Obtener conexión actual
            $connection = $this->getConnectionName();
            // Crear tabla temporal dh21aporte
            $service = app(SicossControlService::class);
            $service->setConnection($connection);
            $service->crearTablaDH21Aportes($this->year, $this->month);

            // Realizar las consultas de conteo
            $conteos = [
                'dh21aporte' => DB::connection($connection)
                    ->table('dh21aporte')
                    ->count(),

                'sicoss_calculos' => DB::connection($connection)
                    ->table('suc.afip_mapuche_sicoss_calculos')
                    ->count(),

                'sicoss' => DB::connection($connection)
                    ->table('suc.afip_mapuche_sicoss')
                    ->count(),
            ];

            // Crear vista para la notificación
            $viewContent = ViewFacade::make('filament.afip.notifications.control-sicoss-conteos', [
                'conteos' => $conteos,
                'year' => $this->year,
                'month' => $this->month,
            ])->render();

            // Mostrar notificación con los resultados
            Notification::make()
                ->success()
                ->title('Control de Conteos Ejecutado')
                ->body($viewContent)
                ->persistent()
                ->send();

            // Guardar los resultados en la sesión para posible uso posterior
            session()->put('sicoss_conteos', $conteos);
        } catch (\Exception $e) {
            logger()->error('Error en ejecución de control de conteos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('Error en el control de conteos')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        } finally {
            $this->loading = false;
        }
    }

    public function ejecutarControlConceptos(?array $conceptos = null): void
    {
        if ($conceptos !== null) {
            $this->conceptosSeleccionados = $conceptos;
        }
        try {
            $this->loading = true;

            // Obtener conexión actual
            $connection = $this->getConnectionName();

            // Lista de conceptos a controlar
            $conceptosAportes = ConceptosSicossEnum::getAllAportesCodes();
            $conceptosContribuciones = ConceptosSicossEnum::getAllContribucionesCodes();
            $conceptosArt = ConceptosSicossEnum::getContribucionesArtCodes();
            $conceptos = array_merge($conceptosAportes, $conceptosContribuciones, $conceptosArt);
            Log::info('Conceptos a controlar', [
                'conceptos' => $conceptos,
            ]);

            Log::info('Ejecutando control de conceptos', [
                'connection' => $connection,
                'year' => $this->year,
                'month' => $this->month,
            ]);

            // Ejecutar la consulta
            $resultados = DB::connection($connection)
                ->table(DB::raw('(SELECT * FROM mapuche.dh21 UNION ALL SELECT * FROM mapuche.dh21h) as h21'))
                ->join('mapuche.dh12 as h12', function ($join): void {
                    $join->on('h21.codn_conce', '=', 'h12.codn_conce');
                })
                ->whereIn('h21.codn_conce', $conceptos)
                ->whereIn('h21.nro_liqui', function ($query): void {
                    $query->select('d22.nro_liqui')
                        ->from('mapuche.dh22 as d22')
                        ->where('d22.sino_genimp', true)
                        ->where('d22.per_liano', $this->year)
                        ->where('d22.per_limes', $this->month);
                })
                ->groupBy('h21.codn_conce', 'h12.desc_conce')
                ->orderBy('h21.codn_conce')
                ->select('h21.codn_conce', 'h12.desc_conce', DB::raw('SUM(impp_conce)::numeric(15, 2) as importe'))
                ->get();

            // Eliminar registros anteriores para este período
            ControlConceptosPeriodo::where('year', $this->year)
                ->where('month', $this->month)
                ->where('connection_name', $connection)
                ->delete();

            // Guardar los nuevos resultados
            foreach ($resultados as $resultado) {
                ControlConceptosPeriodo::create([
                    'year' => $this->year,
                    'month' => $this->month,
                    'codn_conce' => $resultado->codn_conce,
                    'desc_conce' => $resultado->desc_conce,
                    'importe' => $resultado->importe,
                    'connection_name' => $connection,
                ]);
            }

            // Calcular totales para la notificación
            $totalAportes = $resultados->whereIn('codn_conce', $conceptosAportes)
                ->sum('importe');

            $totalContribuciones = $resultados->whereIn('codn_conce', $conceptosContribuciones)
                ->sum('importe');

            // Crear vista para la notificación
            $viewContent = ViewFacade::make('filament.afip.notifications.control-conceptos', [
                'resultados' => $resultados,
                'totalAportes' => $totalAportes,
                'totalContribuciones' => $totalContribuciones,
                'year' => $this->year,
                'month' => $this->month,
            ])->render();

            // Mostrar notificación con los resultados
            Notification::make()
                ->success()
                ->title('Control de Conceptos Ejecutado')
                ->body($viewContent)
                ->actions([
                    NotificationAction::make('ver_detalles')
                        ->label('Ver Detalles')
                        ->color('primary')
                        ->icon('heroicon-o-document-text')
                        ->action(fn () => $this->activeTab = 'conceptos'),
                ])
                ->persistent()
                ->send();
        } catch (\Exception $e) {
            logger()->error('Error en ejecución de control de conceptos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('Error en el control de conceptos')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        } finally {
            $this->loading = false;
        }
    }

    public function viewRecord(): array
    {
        return [
            'tabs' => [
                'resumen' => 'Resumen',
                'diferencias_aportes' => 'Diferencias por Aportes',
                'diferencias_contribuciones' => 'Diferencias por Contribuciones',
            ],
        ];
    }

    public function updatedActiveTab(): void
    {
        $this->record = null;
    }

    public function cargarResumen(): void
    {
        $this->cachedStats = $this->getResumenStats();
        $this->dispatch('resumen-loaded');
    }

    public function buscar(): void
    {
        $this->dispatch('buscar', search: $this->search);
    }

    /**
     * Obtiene los datos para la vista de resumen.
     */
    protected function getViewData(): array
    {
        $data = [
            'tabs' => [
                'resumen' => 'Resumen',
                'diferencias_aportes' => 'Diferencias por Aportes',
                'diferencias_contribuciones' => 'Diferencias por Contribuciones',
                'diferencias_cuils' => 'CUILs no encontrados',
                'conceptos' => 'Conceptos por Período',
            ],
        ];

        // Agregar datos de conteos si están disponibles
        if ($this->hasConteosData()) {
            $data['conteos'] = $this->getConteosData();
            $data['diferencia_conteos'] = $this->getDiferenciaConteos();
        }

        return $data;
    }

    protected function hasResults(): bool
    {
        return $this->resultadosControles !== null &&
            isset($this->resultadosControles['aportes_contribuciones']);
    }

    protected function getResumenStats(): array
    {
        try {
            return cache()->remember('sicoss_resumen_stats', now()->addMinutes(5), function () {
                // Creamos la tabla temporal primero
                $service = app(SicossControlService::class);
                $service->setConnection($this->getConnectionName());
                $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
                $this->year = $periodoFiscal['year'];
                $this->month = $periodoFiscal['month'];
                $service->crearTablaDH21Aportes($this->year, $this->month);

                $data = [
                    'totales' => [
                        'cuils_procesados' => ControlAportesDiferencia::count(),
                        'cuils_con_diferencias_aportes' => ControlAportesDiferencia::where(DB::raw('ABS(diferencia)'), '>', 1)->count(),
                        'cuils_con_diferencias_contribuciones' => ControlContribucionesDiferencia::where(DB::raw('ABS(diferencia)'), '>', 1)->count(),
                    ],
                    'diferencias_por_dependencia' => app(SicossControlService::class)
                        ->setConnection($this->getConnectionName())
                        ->getDiferenciasPorDependencia(),
                    'totales_monetarios' => [
                        'diferencia_aportes' => ControlAportesDiferencia::sum('diferencia'),
                        'diferencia_contribuciones' => ControlContribucionesDiferencia::sum('diferencia'),
                    ],
                    'comparacion_931' => [
                        'aportes' => [
                            'dh21' => DB::connection($this->getConnectionName())->table('dh21aporte')->sum(DB::raw('aportesijpdh21 + aporteinssjpdh21')),
                            'sicoss' => DB::connection($this->getConnectionName())->table('suc.afip_mapuche_sicoss_calculos')
                                ->sum(DB::raw('aportesijp + aporteinssjp + aportediferencialsijp + aportesres33_41re')),
                        ],
                        'contribuciones' => [
                            'dh21' => DB::connection($this->getConnectionName())->table('dh21aporte')->sum(DB::raw('contribucionsijpdh21 + contribucioninssjpdh21')),
                            'sicoss' => DB::connection($this->getConnectionName())->table('suc.afip_mapuche_sicoss_calculos')->sum(DB::raw('contribucionsijp + contribucioninssjp')),
                        ],
                    ],
                    'cuils_no_encontrados' => [
                        'en_dh21' => DB::connection($this->getConnectionName())
                            ->table('dh21aporte')
                            ->whereNotIn('cuil', function ($query): void {
                                $query->select('cuil')->from('suc.afip_mapuche_sicoss_calculos');
                            })
                            ->count(),
                        'en_sicoss' => DB::connection($this->getConnectionName())
                            ->table('suc.afip_mapuche_sicoss_calculos')
                            ->whereNotIn('cuil', function ($query): void {
                                $query->select('cuil')->from('dh21aporte');
                            })
                            ->count(),
                    ],
                ];

                return $data;
            });
        } catch (\Exception $e) {
            logger()->error('Error obteniendo resumen stats:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('Error cargando resumen')
                ->body('No se pudieron cargar los datos del resumen.')
                ->send();

            return [];
        }
    }

    protected function getTableActions(): array
    {
        return match ($this->activeTab) {
            'diferencias_aportes' => [
                TableAction::make('view_aportes')
                    ->label('Ver detalles')
                    ->icon('heroicon-m-eye')
                    ->modalContent(function ($record): View {
                        return view('filament.afip.pages.partials.sicoss-detalle-aportes-modal', [
                            'record' => $record,
                            'sicossCalculo' => $record->sicossCalculo,
                            'relacionActiva' => $record->relacionActiva,
                            'dh01' => $record->dh01,
                        ]);
                    })
                    ->modalHeading(fn ($record) => "Detalles de Aportes - CUIL: {$record->cuil}")
                    ->modalWidth(MaxWidth::SevenExtraLarge),
            ],
            'diferencias_contribuciones' => [
                TableAction::make('view_contribuciones')
                    ->label('Ver detalles')
                    ->icon('heroicon-m-eye')
                    ->modalContent(function ($record): View {
                        return view('filament.afip.pages.partials.sicoss-detalle-contribuciones-modal', [
                            'record' => $record,
                            'sicossCalculo' => $record->sicossCalculo,
                            'relacionActiva' => $record->relacionActiva,
                            'dh01' => $record->dh01,
                        ]);
                    })
                    ->modalHeading(fn ($record) => "Detalles de Contribuciones - CUIL: {$record->cuil}")
                    ->modalWidth(MaxWidth::SevenExtraLarge),
            ],
            'diferencias_cuils' => [
                TableAction::make('view_cuils')
                    ->label('Ver detalles')
                    ->icon('heroicon-m-eye')
                    ->modalContent(function ($record): View {
                        return view('filament.afip.pages.partials.sicoss-detalle-cuils-modal', [
                            'record' => $record,
                        ]);
                    })
                    ->modalHeading(fn ($record) => "Detalles de CUILs - CUIL: {$record->cuil}")
                    ->modalWidth(MaxWidth::SevenExtraLarge),
            ],
            'conceptos' => [
                TableAction::make('view_conceptos')
                    ->label('Ver detalles')
                    ->icon('heroicon-m-eye')
                    ->modalContent(function ($record): View {
                        return view('filament.afip.pages.partials.sicoss-detalle-conceptos-modal', [
                            'record' => $record,
                        ]);
                    })
                    ->modalHeading(fn ($record) => "Detalles de Conceptos - Código: {$record->codn_conce}")
                    ->modalWidth(MaxWidth::SevenExtraLarge),
            ],
            default => [],
        };
    }

    protected function getColumnsForActiveTab(): array
    {
        if ($this->activeTab === 'diferencias_cuils') {
            return $this->getDiferenciasCuilsColumns();
        }

        if ($this->activeTab === 'diferencias_aportes') {
            return $this->getAportesColumns();
        }

        if ($this->activeTab === 'diferencias_contribuciones') {
            return $this->getContribucionesColumns();
        }

        if ($this->activeTab === 'diferencias_art') {
            return $this->getArtColumns();
        }

        if ($this->activeTab === 'conceptos') {
            return $this->getConceptosColumns();
        }

        return [];
    }

    protected function getHeaderActions(): array
    {
        // Acciones base según la pestaña activa
        $actions = match ($this->activeTab) {
            'diferencias_aportes' => [
                EjecutarControlAportesAction::make()
                    ->withPeriodBadge(),
            ],
            'diferencias_contribuciones' => [
                EjecutarControlContribucionesAction::make()->withPeriodBadge(),
            ],
            'diferencias_cuils' => [
                EjecutarControlCuilsAction::make()->withPeriodBadge(),
            ],
            'conceptos' => [
                EjecutarControlConceptosAction::make()->withPeriodBadge(),
            ],
            default => [
                Action::make('ejecutarControles')
                    ->label('Ejecutar los Controles')
                    ->icon('heroicon-o-play')
                    ->badge(fn () => \sprintf('%d-%02d', $this->year, $this->month))
                    ->action(function (): void {
                        $this->ejecutarControles();
                    }),
            ],
        };

        // Agregar acciones comunes
        // if ($this->activeTab !== 'conceptos') {
        //     $actions[] = Action::make('ejecutarControlConceptos')
        //         ->label('Control de Conceptos')
        //         ->icon('heroicon-o-document-text')
        //         ->color('gray')
        //         ->action(function () {
        //             $this->ejecutarControlConceptos();
        //         });
        // }

        // Agregar acción de conteo a todas las pestañas
        $actions[] = Action::make('ejecutarControlConteos')
            ->label('Control de Conteos')
            ->icon('heroicon-o-list-bullet')
            ->color('gray')
            ->action(function (): void {
                $this->ejecutarControlConteos();
            });

        // Agregar acción de exportar a todas las pestañas
        $actions[] = Action::make('export')
            ->label('Exportar')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function () {
                return $this->exportActiveTable();
            })
            ->disabled(fn () => false);

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        $widgets = [
            \App\Filament\Widgets\PeriodoFiscalSelectorWidget::class,
        ];
        if ($this->activeTab === 'conceptos') {
            $widgets[] = \App\Filament\Afip\Widgets\ConceptosSeleccionadosWidget::make([
                'conceptosSeleccionados' => $this->conceptosSeleccionados ?? [],
            ]);
        }
        return $widgets;
    }

    protected function getConceptosColumns(): array
    {
        return [
            TextColumn::make('codn_conce')
                ->label('Código')
                ->searchable()
                ->sortable(),

            TextColumn::make('desc_conce')
                ->label('Descripción')
                ->searchable()
                ->sortable()
                ->wrap(),

            TextColumn::make('importe')
                ->label('Importe')
                ->money('ARS')
                ->sortable(),

            TextColumn::make('created_at')
                ->label('Fecha de Control')
                ->dateTime()
                ->sortable(),
        ];
    }

    protected function exportActiveTable()
    {
        $data = match ($this->activeTab) {
            'resumen' => [
                'class' => ResumenDependenciasExport::class,
                'filename' => 'resumen-diferencias-dependencias',
                'title' => 'Diferencias por Dependencia',
                'data' => collect($this->getResumenStats()['diferencias_por_dependencia']),
            ],
            'diferencias_aportes' => [
                'class' => AportesDiferenciasExport::class,
                'filename' => 'diferencias-aportes',
                'title' => 'Diferencias por Aportes',
                'data' => ControlAportesDiferencia::query()
                    ->with(['sicossCalculo', 'relacionActiva', 'dh01'])
                    ->get(),
            ],
            'diferencias_contribuciones' => [
                'class' => ContribucionesDiferenciasExport::class,
                'filename' => 'diferencias-contribuciones',
                'title' => 'Diferencias por Contribuciones',
                'data' => ControlContribucionesDiferencia::query()
                    ->with(['sicossCalculo', 'relacionActiva', 'dh01'])
                    ->get(),
            ],
            'diferencias_cuils' => [
                'class' => CuilsDiferenciasExport::class,
                'filename' => 'diferencias-cuils',
                'title' => 'CUILs no encontrados',
                'data' => ControlCuilsDiferencia::query()->get(),
            ],
            'conceptos' => [
                'class' => ConceptosExport::class,
                'filename' => 'conceptos',
                'title' => 'Conceptos por Período',
                'data' => ControlConceptosPeriodo::query()
                    ->where('year', $this->year)
                    ->where('month', $this->month)
                    ->get(),
            ],
            default => null
        };

        if (!$data) {
            Notification::make()
                ->warning()
                ->title('No se puede exportar')
                ->body('No hay datos para exportar en esta pestaña.')
                ->send();
            return;
        }

        // Construir el nombre del archivo con la extensión explícita
        $filename = \sprintf(
            '%s-%d-%02d-%s.xlsx',
            $data['filename'],
            $this->year,
            $this->month,
            now()->format('Ymd-His'),
        );

        // Crear la instancia del exportador con los parámetros adicionales
        $exporter = new ($data['class'])(
            $data['data'],
            $this->year,
            $this->month
        );

        // Especificar el tipo de escritor explícitamente
        return Excel::download(
            $exporter,
            $filename,
            \Maatwebsite\Excel\Excel::XLSX,
        );
    }

    private function conexionesDisponibles(): array
    {
        return collect(config('database.connections'))
            ->filter(fn ($config, $name) => str_starts_with($name, 'pgsql-'))
            ->mapWithKeys(fn ($config, $name) => [$name => $name])
            ->all();
    }
}
