<?php

namespace App\Filament\Afip\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use App\Services\SicossControlService;
use Filament\Notifications\Notification;

class SicossControles extends Page
{
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
                ->modalHeading('Seleccionar Conexión'),

            Action::make('ejecutarControles')
                ->label('Ejecutar Controles')
                ->icon('heroicon-o-play')
                ->disabled(fn() => is_null($this->selectedConnection))
                ->requiresConfirmation()
                ->modalHeading('¿Ejecutar controles SICOSS?')
                ->modalDescription('Esta acción ejecutará todos los controles sobre la conexión seleccionada.')
                ->action(function () {
                    $this->ejecutarControles();
                }),
        ];
    }

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

            Notification::make()
                ->success()
                ->title('Controles ejecutados')
                ->body('Los controles se han ejecutado correctamente')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body($e->getMessage())
                ->send();
        }
    }
}
