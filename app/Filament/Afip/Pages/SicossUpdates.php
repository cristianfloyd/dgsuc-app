<?php

declare(strict_types=1);

namespace App\Filament\Afip\Pages;

use Filament\Pages\Page;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use App\Services\Afip\SicossUpdateService;
use App\Filament\Widgets\MultipleIdLiquiSelector;

class SicossUpdates extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?string $navigationLabel = 'Actualización SICOSS';
    protected static ?string $title = 'Actualización de Datos SICOSS';

    protected static string $view = 'filament.afip.pages.sicoss-updates';

    public array $updateResults = [];
    public bool $isProcessing = false;
    public ?array $selectedIdLiqui = null;

    protected function getHeaderWidgets(): array
    {
        return [
            MultipleIdLiquiSelector::class,
        ];
    }

    #[On('idsLiquiSelected')]
    public function handleIdsLiquiSelected($idsLiqui): void
    {
        Log::info('idsLiquiSelected', ['idsLiqui' => $idsLiqui]);
        $this->selectedIdLiqui = $idsLiqui;
    }

    public function runUpdates(): void
    {
        $this->isProcessing = true;

        try {
            $service = app(SicossUpdateService::class);
            // Pass the selected liquidations to the service if needed
            $this->updateResults = $service->executeUpdates($this->selectedIdLiqui);

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
                ->disabled($this->isProcessing || empty($this->selectedIdLiqui))
                ->requiresConfirmation()
                ->modalDescription('¿Está seguro que desea ejecutar las actualizaciones SICOSS? Este proceso puede tomar varios minutos.')
        ];
    }
}
