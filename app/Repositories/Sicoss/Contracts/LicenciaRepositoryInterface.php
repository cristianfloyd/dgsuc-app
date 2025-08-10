<?php

namespace App\Repositories\Sicoss\Contracts;

interface LicenciaRepositoryInterface
{
    /**
     * Obtiene licencias de protección integral y vacaciones.
     *
     * @param string $where_legajos
     *
     * @return array
     */
    public function getLicenciasProtecintegralVacaciones(string $where_legajos): array;

    /**
     * Obtiene licencias vigentes.
     *
     * @param string $where_legajos
     *
     * @return array
     */
    public function getLicenciasVigentes(string $where_legajos): array;
}
