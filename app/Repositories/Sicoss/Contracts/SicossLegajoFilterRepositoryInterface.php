<?php

namespace App\Repositories\Sicoss\Contracts;

interface SicossLegajoFilterRepositoryInterface
{
    /**
     * Obtiene los legajos filtrados para el proceso SICOSS
     * Maneja filtrado por período retroactivo, licencias y agentes sin liquidación.
     *
     * Extraído del método obtener_legajos() de SicossLegacy tal como está
     */
    public function obtenerLegajos(
        string $codc_reparto,
        string $where_periodo_retro,
        string $where_legajo = ' true ',
        bool $check_lic = false,
        bool $check_sin_activo = false,
    ): array;
}
