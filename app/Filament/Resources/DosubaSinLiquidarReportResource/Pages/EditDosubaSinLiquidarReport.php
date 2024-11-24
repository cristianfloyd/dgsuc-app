<?php

namespace App\Filament\Resources\DosubaSinLiquidarReportResource\Pages;

use App\Filament\Resources\DosubaSinLiquidarReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDosubaSinLiquidarReport extends EditRecord
{
    protected static string $resource = DosubaSinLiquidarReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
