<?php

namespace App\Filament\Afip\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ControlArtDiferencia;
use Filament\Forms\Components\Select;
use App\Models\ControlCuilsDiferencia;
use App\Services\SicossControlService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use App\Filament\Afip\AfipPanelProvider;
use App\Models\ControlAportesDiferencia;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Afip\Pages\Traits\HasSicossControlTables;
use Filament\Support\Enums\Alignment;

class SicossControles extends Page implements HasTable
{
    use InteractsWithTable, HasSicossControlTables;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'SICOSS';
    protected static string $view = 'filament.afip.pages.sicoss-controles';
    protected static ?string $title = 'Controles SICOSS';
    protected static ?string $slug = 'afip/sicoss-controles';

    // Especificamos que pertenece al panel AFIP
    protected static ?string $panel = 'afip';

    public $activeTab = 'resumen';
    public $resultadosControles = null;
    public $selectedConnection = null;
    public $availableConnections = [];

    public function mount()
    {
        // Obtenemos todas las conexiones configuradas
        $connections = config('database.connections');

        // Filtramos solo las conexiones que comienzan con 'pgsql-'
        $this->availableConnections = collect($connections)
            ->filter(function ($config, $name) {
                return str_starts_with($name, 'pgsql-');
            })
            ->mapWithKeys(function ($config, $name) {
                // Creamos un array asociativo con nombre => nombre para el select
                return [$name => $name];
            })
            ->toArray();
    }

    /**
     * Ejecuta todos los controles
     */
    public function ejecutarControles(): void
    {
        if (!$this->selectedConnection) {
            Notification::make()
                ->warning()
                ->title('Conexión no seleccionada')
                ->body('Debe seleccionar una conexión antes de ejecutar los controles')
                ->send();
            return;
        }

        try {
            $service = app(SicossControlService::class);
            $service->setConnection($this->selectedConnection);
            $this->resultadosControles = $service->ejecutarControlesPostImportacion();

            if ($this->resultadosControles['success']) {
                Notification::make()
                    ->success()
                    ->title('Controles ejecutados')
                    ->body('Los controles se han ejecutado correctamente')
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title('Error en los controles')
                    ->body($this->resultadosControles['error'])
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body($e->getMessage())
                ->send();
        }
    }

    /**
     * Configura las acciones de la página
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('seleccionarConexion')
                ->label('Seleccionar Conexión')
                ->icon('heroicon-o-server')
                ->form([
                    Select::make('connection')
                        ->label('Conexión')
                        ->options($this->availableConnections)
                        ->required()
                        ->default($this->selectedConnection)
                        ->columnSpanFull()
                ])
                ->action(function (array $data): void {
                    $this->selectedConnection = $data['connection'];
                    $this->resultadosControles = null;

                    Notification::make()
                        ->success()
                        ->title('Conexión seleccionada')
                        ->body("Se ha seleccionado la conexión: {$this->selectedConnection}")
                        ->send();
                })
                ->modalAlignment(Alignment::Center)
                ->modalWidth('md')
                ->modalSubmitAction('Seleccionar')
                ->modalCancelAction('Cancelar')
                ->modalHeading('Seleccionar Conexión'),

            Action::make('ejecutarControles')
                ->label('Ejecutar Controles')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->disabled(fn() => is_null($this->selectedConnection))
                ->requiresConfirmation()
                ->modalAlignment(Alignment::Center)
                ->modalWidth('md')
                ->modalSubmitAction('Sí, ejecutar')
                ->modalCancelAction('Cancelar')
                ->modalHeading('¿Ejecutar controles SICOSS?')
                ->modalDescription('Esta acción ejecutará todos los controles sobre la conexión seleccionada.')
                ->action(function () {
                    $this->ejecutarControles();
                }),
        ];
    }

    public function getTableQuery(): Builder
    {
        // Si no hay conexión seleccionada o estamos en la pestaña resumen
        if (!$this->selectedConnection || $this->activeTab === 'resumen') {
            // Retornamos una query vacía pero válida
            return ControlCuilsDiferencia::query();
        }

        $baseQuery = match ($this->activeTab) {
            'cuils' => ControlCuilsDiferencia::query(),
            'aportes' => ControlAportesDiferencia::query(),
            'art' => ControlArtDiferencia::query(),
            default => ControlCuilsDiferencia::query()->whereRaw('1 = 0'),
        };

        return $baseQuery
            ->where('connection', $this->selectedConnection)
            ->latest('fecha_control');
    }

    public function getTableColumns(): array
    {
        return $this->getColumnsForActiveTab();
    }

    /**
     * Configura las tablas de resultados
     */
    public function table(Table $table): Table
    {
        // Solo mostramos la tabla si no estamos en el resumen
        // if ($this->activeTab === 'resumen') {
        //     return $table;
        // }

        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->defaultSort('diferencia', 'desc')
            ->filters([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading('No hay diferencias')
            ->emptyStateDescription('No se encontraron diferencias para este control')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    protected function getQueryForActiveTab(): ?Builder
    {
        // Si no hay conexión seleccionada o estamos en la pestaña resumen
        if (!$this->selectedConnection || $this->activeTab === 'resumen') {
            // Retornamos una query vacía pero válida
            return ControlCuilsDiferencia::query()->whereRaw('1 = 0');
        }

        $baseQuery = match ($this->activeTab) {
            'cuils' => ControlCuilsDiferencia::query(),
            'aportes' => ControlAportesDiferencia::query(),
            'art' => ControlArtDiferencia::query(),
            default => ControlCuilsDiferencia::query()->whereRaw('1 = 0'),
        };

        return $baseQuery
            ->where('connection', $this->selectedConnection)
            ->latest('fecha_control');
    }
}
