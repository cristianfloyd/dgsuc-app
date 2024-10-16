<?php

namespace App\Filament\Resources\Reportes\RepOrdenPagoModelResource\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Reportes\RepOrdenPagoModelResource;

class ReporteOrdenPago extends ListRecords
{
    protected static string $resource = RepOrdenPagoModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Acciones específicas para el reporte
        ];
    }
}
