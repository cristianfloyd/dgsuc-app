<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;

class ViewResourcePropertiesAction extends Action
{
    #[\Override]
    public static function make(?string $name = null): static
    {
        return parent::make('view_properties')
            ->label('View Properties')
            ->icon(FilamentIcon::resolve('heroicon-o-eye'))
            ->modalHeading('Resource Properties')
            ->modalContent(fn($livewire): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => view('filament.actions.view-resource-properties', [
                'properties' => $livewire->getPropertyValues(),
            ]))
            ->modalWidth('md');
    }
}
