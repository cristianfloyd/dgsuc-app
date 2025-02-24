<?php

declare(strict_types=1);

namespace App\Filament\Afip\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Services\Afip\SicossUpdateService;

class SicossUpdates extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?string $navigationLabel = 'Actualización SICOSS';
    protected static ?string $title = 'Actualización de Datos SICOSS';

    protected static string $view = 'filament.afip.pages.sicoss-updates';

    public array $updateResults = [];
    public bool $isProcessing = false;

    public function runUpdates(): void
    {
        $this->isProcessing = true;

        try {
            $service = app(SicossUpdateService::class);
            $this->updateResults = $service->executeUpdates();

            if ($this->updateResults['status'] === 'success') {
                Notification::make()
                    ->title('Actualización completada')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error en la actualización')
                    ->danger()
                    ->body($this->updateResults['message'])
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body($e->getMessage())
                ->send();
        } finally {
            $this->isProcessing = false;
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('run_updates')
                ->label('Ejecutar Actualizaciones')
                ->action('runUpdates')
                ->disabled($this->isProcessing)
                ->requiresConfirmation()
                ->modalDescription('¿Está seguro que desea ejecutar las actualizaciones SICOSS? Este proceso puede tomar varios minutos.')
        ];
    }
}
