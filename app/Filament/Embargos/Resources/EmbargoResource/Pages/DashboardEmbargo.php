<?php

namespace App\Filament\Embargos\Resources\EmbargoResource\Pages;

use App\Filament\Embargos\Resources\EmbargoResource;
use App\Models\EmbargoProcesoResult;
use Filament\Resources\Pages\Page;

class DashboardEmbargo extends Page
{
    public array $nroComplementarias = [];

    public int $nroLiquiDefinitiva;

    public int $nroLiquiProxima;

    public bool $insertIntoDh25 = false;

    protected static string $resource = EmbargoResource::class;

    protected static string $view = 'filament.resources.embargo-resource.pages.dashboard-embargo';

    public function setParameters(): void
    {

        $complementariasArray = $this->nroComplementarias;

        // Llamar al método del modelo para ejecutar el proceso
        EmbargoProcesoResult::updateData(
            $complementariasArray,
            $this->nroLiquiDefinitiva,
            $this->nroLiquiProxima,
            $this->insertIntoDh25,
        );

        // Puedes agregar lógica adicional aquí, como mostrar un mensaje de éxito
    }
}
