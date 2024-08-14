<?php

namespace App\Filament\Resources\Dh13Resource\Pages;

use App\Filament\Resources\Dh13Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDh13s extends ListRecords
{
    protected static string $resource = Dh13Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
