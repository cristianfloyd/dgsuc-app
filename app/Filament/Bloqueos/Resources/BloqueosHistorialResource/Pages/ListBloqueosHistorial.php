<?php

namespace App\Filament\Bloqueos\Resources\BloqueosHistorialResource\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Bloqueos\Resources\BloqueosHistorialResource;

class ListBloqueosHistorial extends ListRecords
{
    protected static string $resource = BloqueosHistorialResource::class;

    // Solo lectura: deshabilitar crear, editar, eliminar
    protected function getActions(): array
    {
        return [];
    }
}
