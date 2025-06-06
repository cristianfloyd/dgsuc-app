<?php

namespace App\Repositories\Sicoss\Contracts;

use App\Data\Sicoss\SicossProcessData;

interface SicossLegajoProcessorRepositoryInterface
{
    /**
     * Procesa los legajos filtrados para generar datos SICOSS
     * Maneja cálculos complejos, topes jubilatorios, situaciones de revista y estados
     *
     * Extraído del método procesa_sicoss() de SicossLegacy tal como está
     */
    public function procesarSicoss(
        SicossProcessData $datos,
        int $per_anoct,
        int $per_mesct,
        array $legajos,
        string $nombre_arch,
        ?array $licencias = null,
        bool $retro = false,
        bool $check_sin_activo = false,
        bool $retornar_datos = false
    ): array;
}
