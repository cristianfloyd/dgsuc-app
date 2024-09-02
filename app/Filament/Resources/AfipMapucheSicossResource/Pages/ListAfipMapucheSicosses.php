<?php

namespace App\Filament\Resources\AfipMapucheSicossResource\Pages;

use App\Filament\Resources\AfipMapucheSicossResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAfipMapucheSicosses extends ListRecords
{
    protected static string $resource = AfipMapucheSicossResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
