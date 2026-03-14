<?php

namespace App\Filament\Liquidaciones\Resources\LiquidacionControls\Pages;

use App\Filament\Liquidaciones\Resources\LiquidacionControls\LiquidacionControls\LiquidacionControlResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLiquidacionControls extends ListRecords
{
    protected static string $resource = LiquidacionControlResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
