<?php

namespace App\Filament\Reportes\Resources\OrdenDePagoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Reportes\Resources\OrdenDePagoResource;

class EditReporte extends EditRecord
{
    protected static string $resource = OrdenDePagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
