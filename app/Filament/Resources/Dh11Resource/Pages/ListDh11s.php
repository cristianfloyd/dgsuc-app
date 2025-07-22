<?php

namespace App\Filament\Resources\Dh11Resource\Pages;

use App\Filament\Resources\Dh11Resource;
use App\Filament\Resources\Dh11Resource\Widgets\ActualizarImppBasicWidget;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
