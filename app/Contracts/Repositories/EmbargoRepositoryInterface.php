<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface EmbargoRepositoryInterface
{
    /**
     * Ejecuta el proceso de embargo.
     */
    public function executeEmbargoProcess(
        array $nroComplementarias,
        int $nroLiquiDefinitiva,
        int $nroLiquiProxima,
        bool $insertIntoDh25,
    ): Builder;

    /**
     * Obtiene todos los registros de embargo.
     */
    public function getAllEmbargos(): Collection;

    /**
     * Obtiene embargos por tipo.
     */
    public function getEmbargosByType(int $tipoEmbargo): Collection;
}
