<?php

namespace App\Filament\Reportes\Resources\OrdenDePagoResource\Pages;

use App\Filament\Reportes\Resources\OrdenDePagoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
