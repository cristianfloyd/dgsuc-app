<?php

namespace App\Filament\Resources\MapucheGrupoResource\Pages;

use App\Filament\Resources\MapucheGrupoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

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
                ->url(fn () => static::getResource()::getUrl('manage-legajos', ['record' => $this->record])),
        ];
    }
}
