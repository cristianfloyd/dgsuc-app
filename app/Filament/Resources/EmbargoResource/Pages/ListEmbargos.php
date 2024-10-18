<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use App\Filament\Resources\EmbargoResource;
use App\Filament\Widgets\IdLiquiSelector;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListEmbargos extends ListRecords
{
    protected static string $resource = EmbargoResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PeriodoFiscalSelectorWidget::class,
            IdLiquiSelector::class,
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
