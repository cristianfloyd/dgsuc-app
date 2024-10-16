<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use App\Filament\Resources\EmbargoResource;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use Filament\Resources\Pages\ListRecords;

class ListEmbargos extends ListRecords
{
    protected static string $resource = EmbargoResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PeriodoFiscalSelectorWidget::class,
        ];
    }
}
