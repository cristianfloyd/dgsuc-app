<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use App\Filament\Resources\EmbargoResource;
use App\Filament\Resources\EmbargoResource\Widgets\DisplayPropertiesWidget;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListEmbargos extends ListRecords
{
    protected static string $resource = EmbargoResource::class;

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
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
}
