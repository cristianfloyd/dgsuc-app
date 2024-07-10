<?php

namespace App\Filament\Resources\AfipMapucheParaSicossResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AfipMapucheParaSicossResource;

class ListAfipMapucheParaSicosses extends ListRecords
{
    protected static string $resource = AfipMapucheParaSicossResource::class;

    protected function getTableQuery(): ?Builder
    {
        return AfipMapucheParaSicossResource::getModel()::query()->sicossAll();
    }

}
