<?php

namespace App\Filament\Reportes\Resources\ReporteConceptoListadoResource\Pages;

use App\Filament\Resources\ReporteConceptoListadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReporteConceptoListado extends EditRecord
{
    protected static string $resource = ReporteConceptoListadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
