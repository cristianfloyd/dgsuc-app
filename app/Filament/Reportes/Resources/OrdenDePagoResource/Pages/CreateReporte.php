<?php

namespace App\Filament\Reportes\Resources\OrdenDePagoResource\Pages;

use App\Filament\Reportes\Resources\OrdenDePagoResource;
use Dompdf\FrameDecorator\Page;

class CreateReporte extends Page
{
    protected static string $resource = OrdenDePagoResource::class;
}
