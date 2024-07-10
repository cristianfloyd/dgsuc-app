<?php

namespace App\Filament\Resources\AfipSicossDesdeMapucheResource\Pages;

use App\Filament\Resources\AfipSicossDesdeMapucheResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipSicossDesdeMapuche extends EditRecord
{
    protected static string $resource = AfipSicossDesdeMapucheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
