<?php

namespace App\Filament\Liquidaciones\Resources\CategoriasBasicosResource\Pages;

use App\Filament\Liquidaciones\Resources\CategoriasBasicosResource;
use App\Filament\Resources\Dh11Resource\Widgets\ActualizarImppBasicWidget;
use App\Filament\Widgets\PeriodoFiscalSelectorWidget;
use Filament\Resources\Pages\ListRecords;

class ListCategoriasBasicos extends ListRecords
{
    protected static string $resource = CategoriasBasicosResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ActualizarImppBasicWidget::class,
            PeriodoFiscalSelectorWidget::class,
        ];
    }
}
