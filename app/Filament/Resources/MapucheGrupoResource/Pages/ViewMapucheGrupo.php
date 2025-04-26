<?php

namespace App\Filament\Resources\MapucheGrupoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\MapucheGrupoResource;

class ViewMapucheGrupo extends ViewRecord
{
    protected static string $resource = MapucheGrupoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('administrar_legajos')
                ->label('Administrar Legajos')
                ->icon('heroicon-o-users')
                ->url(fn() => static::getResource()::getUrl('manage-legajos', ['record' => $this->record])),
        ];
    }
}
