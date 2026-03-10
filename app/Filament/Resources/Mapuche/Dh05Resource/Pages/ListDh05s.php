<?php

namespace App\Filament\Resources\Mapuche\Dh05Resource\Pages;

use App\Filament\Resources\Mapuche\Dh05Resource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDh05s extends ListRecords
{
    protected static string $resource = Dh05Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
