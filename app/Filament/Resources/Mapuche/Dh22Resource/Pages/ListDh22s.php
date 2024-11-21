<?php

namespace App\Filament\Resources\Mapuche\Dh22Resource\Pages;

use App\Filament\Resources\Mapuche\Dh22Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDh22s extends ListRecords
{
    protected static string $resource = Dh22Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
