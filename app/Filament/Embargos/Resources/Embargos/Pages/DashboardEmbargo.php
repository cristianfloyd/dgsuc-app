<?php

namespace App\Filament\Embargos\Resources\Embargos\Pages;

use App\Filament\Embargos\Resources\Embargos\EmbargoResource;
use App\Models\EmbargoProcesoResult;
use Filament\Resources\Pages\Page;

class DashboardEmbargo extends Page
{
    public array $nroComplementarias = [];

    public int $nroLiquiDefinitiva;

    public int $nroLiquiProxima;

    public bool $insertIntoDh25 = false;

    protected static string $resource = EmbargoResource::class;

    protected string $view = 'filament.resources.embargo-resource.pages.dashboard-embargo';

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
