<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use App\Filament\Resources\EmbargoResource;
use App\Filament\Resources\EmbargoResource\Widgets\DisplayPropertiesWidget;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use App\Traits\DisplayResourceProperties;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Livewire\Attributes\On;

class ListEmbargos extends ListRecords
{
    use displayResourceProperties;
    protected static string $resource = EmbargoResource::class;
    public array $periodoFiscal = [];
    protected EmbargoResource $embargoResource;

    public function mount(): void
    {
        $this->embargoResource = new EmbargoResource;
    }
    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::ScreenExtraLarge;
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
                ->label('Configure Parameters')
                ->url(static::getResource()::getUrl('configure'))
                ->icon('heroicon-o-cog'),
        ];
    }

    #[On('updated-periodo-fiscal')]
    public function updatedPeriodoFiscal($periodoFiscal): void
    {
        $currentProperties = new EmbargoResource();
        $this->periodoFiscal = $periodoFiscal;
        $updatedProperties = array_merge($currentProperties, $this->periodoFiscal);
        dump($updatedProperties);
    }

    protected function getDefaultProperties(): array
    {
        return [];
    }
}
