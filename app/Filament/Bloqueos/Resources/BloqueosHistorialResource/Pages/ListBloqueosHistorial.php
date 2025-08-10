<?php

namespace App\Filament\Bloqueos\Resources\BloqueosHistorialResource\Pages;

use App\Filament\Bloqueos\Resources\BloqueosHistorialResource;
use Filament\Resources\Pages\ListRecords;

class ListBloqueosHistorial extends ListRecords
{
    protected static string $resource = BloqueosHistorialResource::class;

    // Solo lectura: deshabilitar crear, editar, eliminar
    protected function getActions(): array
    {
        return [];
    }
}
