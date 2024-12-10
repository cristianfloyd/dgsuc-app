<?php

namespace App\Filament\Resources\ReporteResource\Pages;


use Filament\Resources\Pages\CreateRecord;
use App\Filament\Reportes\Resources\OrdenDePagoResource;

class CreateReporte extends CreateRecord
{
    protected static string $resource = OrdenDePagoResource::class;
}
