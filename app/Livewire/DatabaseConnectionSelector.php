<?php

namespace App\Livewire;

use App\Services\DatabaseConnectionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Support\Facades\Cookie;

class DatabaseConnectionSelector extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $connection = null;

    public function mount(): void
    {
        $service = app(DatabaseConnectionService::class);
        
        $currentConnection = $service->getCurrentConnection();
        $this->connection = is_string($currentConnection) ? $currentConnection : null;
        
        $this->form->fill([
            'connection' => $this->connection,
        ]);
    }

    public function form(Form $form): Form
    {
        $service = app(DatabaseConnectionService::class);

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
                            'pgsql-liquibase' => 'bg-yellow-500',
                        ];
                        return [
                            'class' => 'fi-compact ' . ($colorClasses[$state] ?? ''),
                        ];
                    })
                    ->afterStateUpdated(function ($state) use ($service) {
                        if (is_string($state)) {
                            $service->setConnection($state);
                            
                            // Guardar en cookie para persistencia entre sesiones
                            Cookie::queue(
                                DatabaseConnectionService::SESSION_KEY, 
                                $state, 
                                60*24*30 // 30 días
                            );
                            
                            $this->dispatch('connection-changed', [
                                'message' => 'Conexión cambiada a ' . ($service->getAvailableConnections()[$state] ?? $state)
                            ]);
                            
                            $this->redirect(request()->header('Referer'));
                        }
                    }),
            ])
            // ->statePath('connection')
            ->extraAttributes([
                'class' => 'fi-compact-form',
            ]);
    }

    public function render()
    {
        return view('livewire.database-connection-selector');
    }
}

