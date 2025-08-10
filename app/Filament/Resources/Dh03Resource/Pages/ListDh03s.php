<?php

namespace App\Filament\Resources\Dh03Resource\Pages;

use App\Filament\Resources\Dh03Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDh03s extends ListRecords
{
    protected static string $resource = Dh03Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
