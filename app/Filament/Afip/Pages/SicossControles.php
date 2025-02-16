<?php

namespace App\Filament\Afip\Pages;

use Filament\Pages\Page;
use App\Models\DH21Aporte;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Models\ControlArtDiferencia;
use App\Traits\SicossConnectionTrait;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use App\Models\ControlCuilsDiferencia;
use App\Services\SicossControlService;
use App\Traits\MapucheConnectionTrait;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Models\ControlAportesDiferencia;
use Filament\Notifications\Notification;
use Filament\Tables\Concerns\InteractsWithTable;

class SicossControles extends Page implements HasTable
{
    use InteractsWithTable;
    use SicossConnectionTrait;
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

    public function mount()
    {
        $connections = config('database.connections');

        $this->availableConnections = collect($connections)
            ->filter(function ($config, $name) {
                return str_starts_with($name, 'pgsql-');
            })
            ->mapWithKeys(function ($config, $name) {
                return [$name => $name];
            })
            ->all(); // Usar all() para obtener un array plano

        // Opcional: para debug
        logger()->info('Available Connections', $this->availableConnections);

        $this->selectedConnection = $this->selectedConnection ?? $this->getConnectionName();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ejecutarControles')
                ->label('Ejecutar Controles')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->modalHeading('¿Ejecutar controles SICOSS?')
                ->modalDescription('Esta acción ejecutará los controles de aportes.')
                ->action(function () {
                    $this->ejecutarControles();
                }),
        ];
    }

    public function ejecutarControles(): void
    {
        try {
            // Indicador de progreso
            $this->loading = true;

            // Instanciamos y configuramos el servicio
            $service = app(SicossControlService::class);
            $service->setConnection($this->getConnectionName());

            // Ejecutamos los controles
            $this->resultadosControles = $service->ejecutarControlesPostImportacion();

            // Notificación de éxito con más detalles
            Notification::make()
                ->success()
                ->title('Controles ejecutados')
                ->body(sprintf(
                    'Se encontraron %d diferencias en CUILs y %d en dependencias',
                    $this->getDiferenciasCount(),
                    $this->getDependenciasCount()
                ))
                ->actions([
                    Action::make('ver_resumen')
                        ->label('Ver Resumen')
                        ->action(fn () => $this->activeTab = 'resumen'),
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
                'diferencias_cuil' => 'Diferencias por CUIL',
                'diferencias_dependencia' => 'Diferencias por Dependencia',
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

    public function table(Table $table): Table
    {
        return $table
            ->query(ControlAportesDiferencia::query())
            ->columns([
                TextColumn::make('cuil')
                    ->label('CUIL')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('aportesijpdh21')
                    ->label('Aportes DH21')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('aporteinssjpdh21')
                    ->label('Aportes INSSJP')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->money('ARS')
                    ->sortable()
                    ->alignment(Alignment::Right)
                    ->color(fn ($record) => $record->diferencia > 0 ? 'danger' : 'success')
            ])
            ->defaultSort('diferencia', 'desc')
            ->striped()
            ->paginated([25, 50, 100])
            ->searchable();
    }

    protected function getColumnsForActiveTab(): array
    {
        if ($this->activeTab === 'diferencias_cuil') {
            return [
                TextColumn::make('cuil')
                    ->label('CUIL')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('diferencia_aportes')
                    ->label('Diferencia Aportes')
                    ->money('ARS')
                    ->sortable()
                    ->alignment('right'),
                TextColumn::make('diferencia_contribuciones')
                    ->label('Diferencia Contribuciones')
                    ->money('ARS')
                    ->sortable()
                    ->alignment('right'),
            ];
        }

        if ($this->activeTab === 'diferencias_dependencia') {
            return [
                TextColumn::make('codc_uacad')
                    ->label('Dependencia')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('caracter')
                    ->label('Carácter')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('diferencia_total')
                    ->label('Diferencia Total')
                    ->money('ARS')
                    ->sortable()
                    ->alignment('right'),
            ];
        }

        return [];
    }
}
