<?php

namespace App\Filament\Reportes\Resources\DosubaSinLiquidarResource\Pages;

use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Models\Reportes\DosubaSinLiquidarModel;
use App\Filament\Reportes\Resources\DosubaSinLiquidarResource;

class ListDosubaSinLiquidars extends ListRecords
{
    protected static string $resource = DosubaSinLiquidarResource::class;



    public function mount(): void
    {
        parent::mount();

        // Aseguramos que la tabla exista antes de cualquier operación
        DosubaSinLiquidarModel::createTableIfNotExists();
        // Limpiamos registros antiguos
        DosubaSinLiquidarModel::cleanOldRecords();
    }
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Generar Reporte')
        ];
    }
}
