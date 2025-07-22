<?php

namespace App\Filament\Embargos\Resources\EmbargoResource\Pages;

use App\Filament\Embargos\Resources\EmbargoResource;
use App\Filament\Embargos\Resources\EmbargoResource\Widgets\DisplayPropertiesWidget;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use App\Services\EmbargoTableService;
use App\Traits\DisplayResourceProperties;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Livewire\Attributes\On;

class ListEmbargos extends ListRecords
{
    use displayResourceProperties;

    public array $periodoFiscal = [];

    protected static string $resource = EmbargoResource::class;

    protected EmbargoResource $embargoResource;

    protected EmbargoTableService $tableService;

    public function boot(EmbargoTableService $tableService): void
    {
        $this->tableService = $tableService;
        $this->tableService->ensureTableExists();
    }

    public function mount(): void
    {
        $this->embargoResource = new EmbargoResource();
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::ScreenExtraLarge;
    }

    #[On('updated-periodo-fiscal')]
    public function updatedPeriodoFiscal($periodoFiscal): void
    {
        $instance = new EmbargoResource();
        $currentProperties = $instance->getPropertiesToDisplay();
        $this->periodoFiscal = [
            'periodoFiscal' => $periodoFiscal,
        ];
        $updatedProperties = array_merge($currentProperties, $this->periodoFiscal);
        $instance->setPropertyValues($updatedProperties);
        $this->dispatch('propertiesUpdated', $updatedProperties);
    }

    protected function getHeaderWidgets(): array
    {
        $embargoResource = new EmbargoResource();
        $data = $embargoResource->getPropertiesToDisplay();

        return [
            PeriodoFiscalSelectorWidget::class,
            DisplayPropertiesWidget::make(properties: [$data]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('configure')
                ->label('Configurar Parametros')
                ->url(static::getResource()::getUrl('configure'))
                ->icon('heroicon-o-cog'),
            Action::make('reset')
                ->label('Reset')
                ->action(function (): void {
                    $instance = new EmbargoResource();
                    $instance->resetPropertiesToDefault();
                    $this->dispatch('propertiesUpdated', $instance->getPropertiesToDisplay());
                }),
        ];
    }

    protected function getDefaultProperties(): array
    {
        return [];
    }
}
