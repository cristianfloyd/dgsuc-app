<?php

namespace App\Filament\Liquidaciones\Resources\LiquidacionControls\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Liquidaciones\Resources\LiquidacionControls\LiquidacionControlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLiquidacionControls extends ListRecords
{
    protected static string $resource = LiquidacionControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
