<?php

namespace App\Filament\Reportes\Resources\ReporteConceptoListados\Pages;

use App\Filament\Resources\ReporteConceptoListadoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReporteConceptoListado extends EditRecord
{
    protected static string $resource = ReporteConceptoListadoResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
