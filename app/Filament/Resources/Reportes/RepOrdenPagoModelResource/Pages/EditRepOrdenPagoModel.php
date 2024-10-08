<?php

namespace App\Filament\Resources\Reportes\RepOrdenPagoModelResource\Pages;

use App\Filament\Resources\Reportes\RepOrdenPagoModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepOrdenPagoModel extends EditRecord
{
    protected static string $resource = RepOrdenPagoModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
