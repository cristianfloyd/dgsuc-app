<?php

namespace App\Filament\Reportes\Resources\DosubaSinLiquidars\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Reportes\Resources\DosubaSinLiquidars\DosubaSinLiquidars\DosubaSinLiquidarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDosubaSinLiquidar extends EditRecord
{
    protected static string $resource = DosubaSinLiquidarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
