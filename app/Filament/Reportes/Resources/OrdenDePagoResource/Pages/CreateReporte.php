<?php

namespace App\Filament\Reportes\Resources\OrdenDePagoResource\Pages;


use Dompdf\FrameDecorator\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use App\Filament\Reportes\Resources\OrdenDePagoResource;

class CreateReporte extends Page
{
    protected static string $resource = OrdenDePagoResource::class;


}
