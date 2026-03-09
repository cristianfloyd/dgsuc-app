<?php

namespace App\Contracts;

use App\NroLiqui;
use Illuminate\Database\Eloquent\Builder;

interface Dh21RepositoryInterface
{
    public function query(): Builder;

    public function getDistinctLegajos(): int;

    public function getTotalConcepto101(?NroLiqui $nroLiqui = null): float;

    /**
     * Obtiene conceptos liquidados para procesamiento SICOSS.
     */
    public function obtenerConceptosLiquidadosSicoss(int $per_anoct, int $per_mesct, string $where): array;

    /**
     * Obtiene períodos retro disponibles de la tabla temporal pre_conceptos_liquidados.
     */
    public function obtenerPeriodosRetro(bool $check_lic = false, bool $check_retr = false): array;
}
