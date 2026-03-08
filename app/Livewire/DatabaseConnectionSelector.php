<?php

namespace App\Livewire;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Schema;
use App\Services\EnhancedDatabaseConnectionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DatabaseConnectionSelector extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
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

    public function form(Schema $schema): Schema
    {
        $service = app(EnhancedDatabaseConnectionService::class);

        return $schema
            ->components([
                Select::make('connection')
                    ->hiddenLabel()
                    ->options($service->getAvailableConnections())
                    ->live()
                    ->extraAttributes(function ($state) {
                        $colorClasses = [
                            'pgsql-mapuche' => 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400',
                            'pgsql-prod' => 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400',
                            'pgsql-liqui' => 'bg-yellow-50 border-yellow-200 text-yellow-700 dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-400',
                        ];
                        return [
                            'class' => 'text-xs font-medium h-9 ' . ($colorClasses[$state] ?? ''),
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
