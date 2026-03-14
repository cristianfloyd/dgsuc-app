<?php

namespace App\Repositories\Sicoss\Contracts;

interface LicenciaRepositoryInterface
{
    /**
     * Obtiene licencias de protección integral y vacaciones.
     *
     *
     */
    public function getLicenciasProtecintegralVacaciones(string $where_legajos): array;

    /**
     * Obtiene licencias vigentes.
     *
     *
     */
    public function getLicenciasVigentes(string $where_legajos): array;
}
