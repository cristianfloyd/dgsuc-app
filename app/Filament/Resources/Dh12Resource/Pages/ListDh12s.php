<?php

namespace App\Filament\Resources\Dh12Resource\Pages;

use App\Filament\Resources\Dh12Resource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDh12s extends ListRecords
{
    protected static string $resource = Dh12Resource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
