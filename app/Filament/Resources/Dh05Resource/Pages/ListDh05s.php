<?php

namespace App\Filament\Resources\Dh05Resource\Pages;

use App\Filament\Resources\Dh05Resource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDh05s extends ListRecords
{
    protected static string $resource = Dh05Resource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
