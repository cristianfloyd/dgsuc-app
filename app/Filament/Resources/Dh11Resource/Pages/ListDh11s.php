<?php

namespace App\Filament\Resources\Dh11Resource\Pages;

use App\Filament\Resources\Dh11Resource;
use App\Filament\Resources\Dh11Resource\Widgets\ActualizarImppBasicWidget;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDh11s extends ListRecords
{
    protected static string $resource = Dh11Resource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    #[\Override]
    protected function getHeaderWidgets(): array
    {
        return [
            ActualizarImppBasicWidget::class,
            PeriodoFiscalSelectorWidget::class,
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
