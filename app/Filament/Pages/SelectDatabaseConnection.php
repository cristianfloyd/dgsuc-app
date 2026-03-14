<?php

namespace App\Filament\Pages;

use App\Services\DatabaseConnectionService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class SelectDatabaseConnection extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $selectedConnection = null;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Cambiar Base de Datos';

    protected static ?string $title = 'Seleccionar Base de Datos';

    protected static ?int $navigationSort = 100;

    public function mount(): void
    {
        $databaseConnectionService = resolve(DatabaseConnectionService::class);
        $this->selectedConnection = $databaseConnectionService->getCurrentConnection();
        $this->form->fill([
            'connection' => $this->selectedConnection,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $databaseConnectionService = resolve(DatabaseConnectionService::class);

        return $schema
            ->components([
                Select::make('connection')
                    ->label('Conexión a Base de Datos')
                    ->options($databaseConnectionService->getAvailableConnections())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (string $state) use ($databaseConnectionService): void {
                        $databaseConnectionService->setConnection($state);

                        Notification::make()
                            ->title('Conexión cambiada')
                            ->body('La conexión a la base de datos ha sido cambiada a ' . $databaseConnectionService->getAvailableConnections()[$state])
                            ->success()
                            ->send();

                        $this->redirect(request()->header('Referer', route('filament.admin.pages.dashboard')));
                    }),
            ]);
    }

    #[\Override]
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
