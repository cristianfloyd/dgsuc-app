<?php

namespace App\Filament\Reportes\Resources\OrdenDePagos\Pages;

use App\Filament\Reportes\Resources\OrdenDePagos\OrdenDePagos\OrdenDePagoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReporte extends EditRecord
{
    protected static string $resource = OrdenDePagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
