<?php

namespace App\Repositories\Sicoss\Contracts;

interface Dh03RepositoryInterface
{
    /**
     * Obtiene cargos activos sin licencia para un legajo
     *
     * @param int $legajo
     * @return array
     */
    public function getCargosActivosSinLicencia(int $legajo): array;

    /**
     * Obtiene cargos activos con licencia vigente para un legajo
     *
     * @param int $legajo
     * @return array
     */
    public function getCargosActivosConLicenciaVigente(int $legajo): array;

    /**
     * Obtiene los límites de cargos para un legajo
     *
     * @param int $legajo
     * @return array
     */
    public function getLimitesCargos(int $legajo): array;
}
