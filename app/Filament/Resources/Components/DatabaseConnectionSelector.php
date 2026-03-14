<?php

namespace App\Filament\Resources\Components;

use App\Services\DatabaseConnectionService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DatabaseConnectionSelector extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?string $connection = null;

    public function mount(): void
    {
        $databaseConnectionService = resolve(DatabaseConnectionService::class);
        $currentConnection = $databaseConnectionService->getCurrentConnection();
        dd($currentConnection);
        $this->form->fill([
            'connection' => $this->connection,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $databaseConnectionService = resolve(DatabaseConnectionService::class);

        return $schema
            ->components([
                Select::make('connection')
                    ->label('Base de Datos')
                    ->options($databaseConnectionService->getAvailableConnections())
                    ->live()
                    ->afterStateUpdated(function (string $state) use ($databaseConnectionService): void {
                        $databaseConnectionService->setConnection($state);

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
