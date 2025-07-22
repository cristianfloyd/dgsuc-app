<?php

namespace App\Filament\Resources\Components;

use App\Services\DatabaseConnectionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DatabaseConnectionSelector extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $connection = null;

    public function mount(): void
    {
        $service = app(DatabaseConnectionService::class);
        $currentConnection = $service->getCurrentConnection();
        dd($currentConnection);
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
                    ->label('Base de Datos')
                    ->options($service->getAvailableConnections())
                    ->live()
                    ->afterStateUpdated(function ($state) use ($service): void {
                        $service->setConnection($state);

                        Notification::make()
                            ->title('Conexión cambiada')
                            ->body('La conexión a la base de datos ha sido cambiada')
                            ->success()
                            ->send();

                        $this->redirect(request()->header('Referer'));
                    }),
            ]);
    }

    public function render(): View
    {
        return view('filament.components.database-connection-selector');
    }
}
