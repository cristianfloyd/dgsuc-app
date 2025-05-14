<?php

namespace App\Filament\Reportes\Resources\LicenciaVigenteResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Reportes\Resources\LicenciaVigenteResource;

class ListLicenciaVigentes extends ListRecords
{
    protected static string $resource = LicenciaVigenteResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Consulta de Licencias Vigentes';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Ingrese legajos para consultar sus licencias vigentes en el periodo fiscal actual';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Sin acciones de creación
        ];
    }
}
