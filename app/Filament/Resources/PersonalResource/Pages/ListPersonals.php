<?php

namespace App\Filament\Resources\PersonalResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PersonalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPersonals extends ListRecords
{
    protected static string $resource = PersonalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
