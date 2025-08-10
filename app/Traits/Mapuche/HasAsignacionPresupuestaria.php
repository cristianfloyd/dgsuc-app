<?php

namespace App\Traits\Mapuche;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para manejar la lógica de imputación presupuestaria.
 */
trait HasAsignacionPresupuestaria
{
    /**
     * Calcula el total de imputación por unidad.
     */
    public function getTotalAllocationByUnit(int $codnArea): float
    {
        return $this->where('codn_area', $codnArea)
            ->sum('porc_ipres');
    }

    /**
     * Scope para filtrar imputaciones por programa y subprograma.
     */
    public function scopeByProgram(Builder $query, int $program, int $subProgram): Builder
    {
        return $query->where('codn_progr', $program)
            ->where('codn_subpr', $subProgram);
    }

    /**
     * Verifica si la imputación está dentro del límite permitido.
     */
    public function isAllocationWithinLimit(float $percentage): bool
    {
        $currentTotal = $this->getTotalAllocationByUnit($this->codn_area);
        return ($currentTotal + $percentage) <= 100.0;
    }

    /**
     * Obtiene las imputaciones agrupadas por fuente de financiamiento.
     */
    public function getAllocationsBySource(): array
    {
        return $this->query()
            ->selectRaw('codn_fuent, SUM(porc_ipres) as total')
            ->groupBy('codn_fuent')
            ->get()
            ->toArray();
    }

    /**
     * Valida la estructura completa de imputación.
     */
    public function validateBudgetStructure(): bool
    {
        return !empty($this->codn_progr) &&
               !empty($this->codn_subpr) &&
               !empty($this->codn_proye) &&
               !empty($this->codn_activ) &&
               !empty($this->codn_obra);
    }
}
