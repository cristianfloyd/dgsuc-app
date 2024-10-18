<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use Filament\Actions\Action;
use App\Filament\Widgets\IdLiquiSelector;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\EmbargoResource;
use App\Filament\Widgets\DisplayPropertiesWidget;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;

class ListEmbargos extends ListRecords
{
    protected static string $resource = EmbargoResource::class;

    protected function getHeaderWidgets(): array
    {
        // Crear la instancia del widget
        // $displayPropertiesWidget = new DisplayPropertiesWidget();

        // Inicializar el widget con la clase de recurso
        // $displayPropertiesWidget->initialize(EmbargoResource::class);

        return [
            PeriodoFiscalSelectorWidget::class,
            IdLiquiSelector::class,
            DisplayPropertiesWidget::createWithResource(EmbargoResource::class),
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
