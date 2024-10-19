<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use Filament\Resources\Pages\Page;
use App\Models\EmbargoProcesoResult;
use App\Filament\Resources\EmbargoResource;

class DashboardEmbargo extends Page
{
    protected static string $resource = EmbargoResource::class;

    protected static string $view = 'filament.resources.embargo-resource.pages.dashboard-embargo';

    public array $nroComplementarias = [];
    public int $nroLiquiDefinitiva;
    public int $nroLiquiProxima;
    public bool $insertIntoDh25 = false;

    public function setParameters(): void
    {

        $complementariasArray = $this->nroComplementarias;

        // Llamar al método del modelo para ejecutar el proceso
        EmbargoProcesoResult::updateData(
            $complementariasArray,
            $this->nroLiquiDefinitiva,
            $this->nroLiquiProxima,
            $this->insertIntoDh25
        );

        // Puedes agregar lógica adicional aquí, como mostrar un mensaje de éxito
    }

}
