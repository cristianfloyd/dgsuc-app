<?php

namespace App\Filament\Resources\Dh11Resource\Pages;

use Filament\Actions;
use App\Filament\Resources\Dh11Resource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use App\Filament\Resources\Dh11Resource\Widgets\ActualizarImppBasicWidget;

class ListDh11s extends ListRecords
{
    protected static string $resource = Dh11Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

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
