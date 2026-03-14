<?php

namespace App\Filament\Afip\Resources\AfipMapucheMiSimplificacions\Pages;

use App\Filament\Afip\Resources\AfipMapucheMiSimplificacions\AfipMapucheMiSimplificacions\AfipMapucheMiSimplificacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListAfipMapucheMiSimplificacions extends ListRecords
{
    protected static string $resource = AfipMapucheMiSimplificacionResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
