<?php

namespace App\Filament\Afip\Pages;

use Filament\Pages\Page;
use App\Models\DH21Aporte;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use App\Models\ControlArtDiferencia;
use Filament\Support\Enums\MaxWidth;
use App\Traits\SicossConnectionTrait;
use Filament\Support\Enums\Alignment;
use App\Models\ControlCuilsDiferencia;
use App\Services\SicossControlService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Models\ControlAportesDiferencia;
use Filament\Notifications\Notification;
use App\Models\ControlContribucionesDiferencia;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\Action as TableAction;
use Illuminate\Support\Facades\View as ViewFacade;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Afip\Pages\Traits\HasSicossControlTables;
use Filament\Notifications\Actions\Action as NotificationAction;
use App\Filament\Afip\Resources\RelationManagers\SicossCalculoRelationManager;
use App\Filament\Afip\Resources\RelationManagers\RelacionActivaRelationManager;

class SicossControles extends Page implements HasTable
{
    use InteractsWithTable;
    use SicossConnectionTrait;
    use HasSicossControlTables;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'SICOSS';
    protected static string $view = 'filament.afip.pages.sicoss-controles';
    protected static ?string $title = 'Controles SICOSS';
    protected static ?string $slug = 'afip/sicoss-controles';
    protected static ?string $panel = 'afip';

    public $selectedConnection = null;
    public $availableConnections = [];
    public $activeTab = 'resumen';
    public $resultadosControles = null;
    public $loading = false;
    public $record = null;
    public $cachedStats = null;

    public function mount()
    {
        $this->availableConnections = collect(config('database.connections'))
            ->filter(function ($config, $name) {
                return str_starts_with($name, 'pgsql-');
            })
            ->mapWithKeys(function ($config, $name) {
                return [$name => $name];
            })
            ->all();

        $this->selectedConnection = $this->selectedConnection ?? $this->getConnectionName();
    }

    public function ejecutarControles(): void
    {
        try {
            $this->loading = true;

            Notification::make()
                ->title('Iniciando controles SICOSS')
                ->info()
                ->send();

            $service = app(SicossControlService::class);
            $service->setConnection($this->getConnectionName());

            $this->resultadosControles = $service->ejecutarControlesPostImportacion();

            $diferenciasAportes = abs($this->resultadosControles['totales']['dh21']['aportes'] -
                $this->resultadosControles['totales']['sicoss']['aportes']);

            $diferenciasContribuciones = abs($this->resultadosControles['totales']['dh21']['contribuciones'] -
                $this->resultadosControles['totales']['sicoss']['contribuciones']);

            $viewContent = ViewFacade::make('filament.afip.notifications.control-sicoss', [
                'diferenciasAportes' => number_format($diferenciasAportes, 2, ',', '.'),
                'diferenciasContribuciones' => number_format($diferenciasContribuciones, 2, ',', '.'),
                'totalCuils' => $this->getDiferenciasCount(),
                'totalDependencias' => $this->getDependenciasCount()
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
                        ->action(fn() => $this->activeTab = 'resumen'),
                ])
                ->persistent()
                ->send();

        } catch (\Exception $e) {
            logger()->error('Error en ejecución de controles SICOSS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

    protected function getViewData(): array
    {
        return [
            'tabs' => [
                'resumen' => 'Resumen',
                'diferencias_aportes' => 'Diferencias por Aportes',
                'diferencias_contribuciones' => 'Diferencias por Contribuciones',
                'diferencias_cuils' => 'CUILs no encontrados',
            ],
        ];
    }

    public function formatMoney($value): string
    {
        return '$ ' . number_format($value, 2, ',', '.');
    }

    protected function hasResults(): bool
    {
        return !is_null($this->resultadosControles) &&
            isset($this->resultadosControles['aportes_contribuciones']);
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
        return count($this->resultadosControles['aportes_contribuciones']['diferencias_por_dependencia']);
    }

    protected function getResumenStats(): array
    {
        try {
            return cache()->remember('sicoss_resumen_stats', now()->addMinutes(5), function () {
                // Creamos la tabla temporal primero
                $service = app(SicossControlService::class);
                $service->setConnection($this->getConnectionName());
                $service->crearTablaDH21Aportes();

                return [
                    'totales' => [
                        'cuils_procesados' => ControlAportesDiferencia::count(),
                        'cuils_con_diferencias_aportes' => ControlAportesDiferencia::where(DB::raw('ABS(diferencia)'), '>', 1)->count(),
                        'cuils_con_diferencias_contribuciones' => ControlContribucionesDiferencia::where(DB::raw('ABS(diferencia)'), '>', 1)->count(),
                    ],
                    'diferencias_por_dependencia' => DB::connection($this->getConnectionName())
                        ->table('suc.control_aportes_diferencias')
                        ->select('codc_uacad', 'caracter')
                        ->selectRaw('SUM(diferencia) as diferencia_total')
                        ->groupBy('codc_uacad', 'caracter')
                        ->having(DB::raw('ABS(SUM(diferencia))'), '>', 1)
                        ->get(),
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
                        'en_dh21' => DB::connection($this->getConnectionName())->table('dh21aporte')
                            ->whereNotIn('cuil', function ($query) {
                                $query->select('cuil')->from('suc.afip_mapuche_sicoss_calculos');
                            })
                            ->count(),
                        'en_sicoss' => DB::connection($this->getConnectionName())->table('suc.afip_mapuche_sicoss_calculos')
                            ->whereNotIn('cuil', function ($query) {
                                $query->select('cuil')->from('dh21aporte');
                            })
                            ->count(),
                    ],
                ];
            });
        } catch (\Exception $e) {
            logger()->error('Error obteniendo resumen stats:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->danger()
                ->title('Error cargando resumen')
                ->body('No se pudieron cargar los datos del resumen.')
                ->send();

            return [];
        }
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
            default => ControlAportesDiferencia::query()
        };

        return $table
            ->query($query)
            ->columns($this->getColumnsForActiveTab())
            ->when($this->activeTab !== 'diferencias_cuils', fn (Table $table) =>
                $table->defaultSort('diferencia', 'desc')
            )
            ->striped()
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25, 50, 100])
            ->searchable()
            ->actions($this->getTableActions());
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
                    ->modalHeading(fn($record) => "Detalles de Aportes - CUIL: {$record->cuil}")
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
                    ->modalHeading(fn($record) => "Detalles de Contribuciones - CUIL: {$record->cuil}")
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
                    ->modalHeading(fn($record) => "Detalles de CUILs - CUIL: {$record->cuil}")
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

        return [];
    }

    protected function getHeaderActions(): array
    {
        // Acciones base según la pestaña activa
        return match ($this->activeTab) {
            'diferencias_aportes' => [
                Action::make('ejecutarControlAportes')
                    ->label('Ejecutar Control de Aportes')
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->modalHeading('¿Ejecutar control de aportes?')
                    ->modalDescription('Esta acción ejecutará el control específico de aportes.')
                    ->action(function () {
                        $this->ejecutarControlAportes();
                    }),
            ],
            'diferencias_contribuciones' => [
                Action::make('ejecutarControlContribuciones')
                    ->label('Ejecutar Control de Contribuciones')
                    ->icon('heroicon-o-banknotes')
                    ->requiresConfirmation()
                    ->modalHeading('¿Ejecutar control de contribuciones?')
                    ->modalDescription('Esta acción ejecutará el control específico de contribuciones.')
                    ->action(function () {
                        $this->ejecutarControlContribuciones();
                    }),
            ],
            'diferencias_cuils' => [
                Action::make('ejecutarControlCuils')
                    ->label('Ejecutar Control de CUILs')
                    ->icon('heroicon-o-user-group')
                    ->requiresConfirmation()
                    ->modalHeading('¿Ejecutar control de CUILs?')
                    ->modalDescription('Esta acción verificará los CUILs que existen en un sistema pero no en el otro.')
                    ->action(function () {
                        $this->ejecutarControlCuils();
                    }),
            ],
            default => [
                Action::make('ejecutarControles')
                    ->label('Ejecutar los Controles')
                    ->icon('heroicon-o-play')
                    ->requiresConfirmation()
                    // ->modalHeading('¿Ejecutar controles SICOSS?')
                    // ->modalDescription('Esta acción ejecutará todos los controles disponibles.')
                    ->action(function () {
                        $this->ejecutarControles();
                    })
            ],
        };
    }

    private function verControles(): void
    {
        dump('HOla Mundo');
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

            $resultados = $service->ejecutarControlContribuciones();

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

            $resultados = $service->ejecutarControlCuils();

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

    public function cargarResumen()
    {
        $this->cachedStats = $this->getResumenStats();
        $this->dispatch('resumen-loaded');
    }
}
