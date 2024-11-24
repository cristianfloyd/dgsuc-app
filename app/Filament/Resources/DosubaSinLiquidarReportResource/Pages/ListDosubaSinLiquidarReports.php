<?php

namespace App\Filament\Resources\DosubaSinLiquidarReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\DosubaSinLiquidarReportResource;

class ListDosubaSinLiquidarReports extends ListRecords
{
    protected static string $resource = DosubaSinLiquidarReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
