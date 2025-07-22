<?php

namespace App\Livewire;

use App\Services\EnhancedDatabaseConnectionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DatabaseConnectionSelector extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $connection = null;

    public function mount(): void
    {
        $service = app(EnhancedDatabaseConnectionService::class);

        $currentConnection = $service->getCurrentConnection();
        $this->connection = \is_string($currentConnection) ? $currentConnection : null;

        // Log::debug("DatabaseConnectionSelector montado", [
        //     'connection' => $this->connection
        // ]);

        $this->form->fill([
            'connection' => $this->connection,
        ]);
    }

    public function form(Form $form): Form
    {
        $service = app(EnhancedDatabaseConnectionService::class);

        return $form
            ->schema([
                Select::make('connection')
                    ->hiddenLabel()
                    ->options($service->getAvailableConnections())
                    ->live()
                    ->extraAttributes(function ($state) {
                        $colorClasses = [
                            'pgsql-mapuche' => 'bg-green-500',
                            'pgsql-prod' => 'bg-blue-500',
                            'pgsql-liqui' => 'bg-yellow-500',
                        ];
                        return [
                            'class' => 'fi-compact ' . ($colorClasses[$state] ?? ''),
                        ];
                    })
                    ->afterStateUpdated(function ($state) use ($service): void {
                        if (\is_string($state)) {
                            Log::debug('Cambiando conexión', ['nueva_conexion' => $state]);

                            $service->setConnection($state);

                            $this->dispatch('connection-changed', [
                                'message' => 'Conexión cambiada a ' . ($service->getAvailableConnections()[$state] ?? $state),
                            ]);

                            // Recargar la página para aplicar la nueva conexión
                            $this->redirect(request()->header('Referer'));
                        }
                    }),
            ])
            ->extraAttributes([
                'class' => 'fi-compact-form',
            ]);
    }

    public function render()
    {
        return view('livewire.database-connection-selector');
    }
}
