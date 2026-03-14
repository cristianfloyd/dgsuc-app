<?php

namespace App\Repositories\Sicoss\Contracts;

use App\Models\Dh03;

interface Dh03RepositoryInterface
{
    /**
     * Busca un cargo por número de legajo y número de cargo.
     */
    public function findCargoPorLegajoCargo(int $nroLegaj, int $nroCargo): ?Dh03;

    /**
     * Verifica si existe el par legajo-cargo en dh03.
     */
    public function existeParLegajoCargo(int $nroLegaj, int $nroCargo): bool;

    /**
     * Obtiene cargos activos sin licencia para un legajo.
     */
    public function getCargosActivosSinLicencia(int $legajo): array;

    /**
     * Obtiene cargos activos con licencia vigente para un legajo.
     */
    public function getCargosActivosConLicenciaVigente(int $legajo): array;

    /**
     * Obtiene los límites de cargos para un legajo.
     */
    public function getLimitesCargos(int $legajo): array;

    /**
     * Verifica si un vínculo es válido para una fecha específica.
     *
     * Un vínculo se considera válido si:
     * - Existe en la tabla dh03
     * - La fecha proporcionada coincide con el día siguiente a la fecha de baja
     * - No existe más de un registro relacionado en la tabla dh10
     *
     * @param  string  $fecha  Fecha a verificar en formato compatible con PostgreSQL
     * @param  int  $vinculo  Número de vínculo a validar
     * @return bool True si el vínculo es válido, False en caso contrario
     */
    public function esVinculoValido(string $fecha, int $vinculo): bool;

    /**
     * Verifica si existe una categoría diferencial activa para un legajo.
     */
    public function existeCategoriaDiferencial(int $nroLegajo, string|array $catDiferencial): bool;
}
