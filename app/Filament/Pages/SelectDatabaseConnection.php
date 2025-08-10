<?php

namespace App\Filament\Pages;

use App\Services\DatabaseConnectionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SelectDatabaseConnection extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $selectedConnection = null;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Cambiar Base de Datos';

    protected static ?string $title = 'Seleccionar Base de Datos';

    protected static ?int $navigationSort = 100;

    public function mount(): void
    {
        $service = app(DatabaseConnectionService::class);
        $this->selectedConnection = $service->getCurrentConnection();
        $this->form->fill([
            'connection' => $this->selectedConnection,
        ]);
    }

    public function form(Form $form): Form
    {
        $service = app(DatabaseConnectionService::class);

        return $form
            ->schema([
                Select::make('connection')
                    ->label('Conexión a Base de Datos')
                    ->options($service->getAvailableConnections())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state) use ($service): void {
                        $service->setConnection($state);

                        Notification::make()
                            ->title('Conexión cambiada')
                            ->body('La conexión a la base de datos ha sido cambiada a ' . $service->getAvailableConnections()[$state])
                            ->success()
                            ->send();

                        $this->redirect(request()->header('Referer', route('filament.admin.pages.dashboard')));
                    }),
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
