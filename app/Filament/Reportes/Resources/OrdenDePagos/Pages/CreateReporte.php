<?php

namespace App\Filament\Reportes\Resources\OrdenDePagos\Pages;

use App\Filament\Reportes\Resources\OrdenDePagos\OrdenDePagos\OrdenDePagoResource;
use Dompdf\FrameDecorator\Page;

class CreateReporte extends Page
{
    protected static string $resource = OrdenDePagoResource::class;
}
