<?php

namespace App\Filament\Liquidaciones\Resources\LiquidacionControls\Pages;

use App\Filament\Liquidaciones\Resources\LiquidacionControls\LiquidacionControls\LiquidacionControlResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLiquidacionControl extends EditRecord
{
    protected static string $resource = LiquidacionControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
