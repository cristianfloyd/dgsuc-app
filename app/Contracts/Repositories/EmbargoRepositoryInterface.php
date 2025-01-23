<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface EmbargoRepositoryInterface
{
    /**
     * Ejecuta el proceso de embargo
     *
     * @param array $nroComplementarias
     * @param int $nroLiquiDefinitiva
     * @param int $nroLiquiProxima
     * @param bool $insertIntoDh25
     * @return Builder
     */
    public function executeEmbargoProcess(
        array $nroComplementarias,
        int $nroLiquiDefinitiva,
        int $nroLiquiProxima,
        bool $insertIntoDh25
    ): Builder;

    /**
     * Obtiene todos los registros de embargo
     *
     * @return Collection
     */
    public function getAllEmbargos(): Collection;

    /**
     * Obtiene embargos por tipo
     *
     * @param int $tipoEmbargo
     * @return Collection
     */
    public function getEmbargosByType(int $tipoEmbargo): Collection;
}
