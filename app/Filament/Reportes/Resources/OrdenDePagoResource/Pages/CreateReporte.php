<?php

namespace App\Filament\Reportes\Resources\ReporteResource\Pages;


use Filament\Resources\Pages\CreateRecord;
use App\Filament\Reportes\Resources\OrdenDePagoResource;

class CreateReporte extends CreateRecord
{
    protected static string $resource = OrdenDePagoResource::class;
}
