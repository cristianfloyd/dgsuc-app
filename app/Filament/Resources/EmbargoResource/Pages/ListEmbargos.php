<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use Livewire\Livewire;
use Filament\Actions\Action;
use App\Filament\Widgets\IdLiquiSelector;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\EmbargoResource;
use App\Livewire\DynamicPropertiesComponent;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use App\Filament\Resources\EmbargoResource\Widgets\DisplayPropertiesWidget;

class ListEmbargos extends ListRecords
{
    protected static string $resource = EmbargoResource::class;

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
}
