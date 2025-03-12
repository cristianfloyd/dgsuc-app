<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;
use App\Services\DatabaseConnectionService;
use Filament\Forms\Concerns\InteractsWithForms;

class SelectDatabaseConnection extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationLabel = 'Cambiar Base de Datos';
    protected static ?string $title = 'Seleccionar Base de Datos';
    protected static ?int $navigationSort = 100;

    public ?string $selectedConnection = null;

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
                    ->afterStateUpdated(function ($state) use ($service) {
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
