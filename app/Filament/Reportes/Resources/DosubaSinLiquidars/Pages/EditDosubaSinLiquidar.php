<?php

namespace App\Filament\Reportes\Resources\DosubaSinLiquidars\Pages;

use App\Filament\Reportes\Resources\DosubaSinLiquidars\DosubaSinLiquidars\DosubaSinLiquidarResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDosubaSinLiquidar extends EditRecord
{
    protected static string $resource = DosubaSinLiquidarResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
