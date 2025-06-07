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

    /**
     * Verifica si un vínculo es válido para una fecha específica.
     *
     * Un vínculo se considera válido si:
     * - Existe en la tabla dh03
     * - La fecha proporcionada coincide con el día siguiente a la fecha de baja
     * - No existe más de un registro relacionado en la tabla dh10
     *
     * @param string $fecha Fecha a verificar en formato compatible con PostgreSQL
     * @param int $vinculo Número de vínculo a validar
     * @return bool True si el vínculo es válido, False en caso contrario
     */
    public static function esVinculoValido(string $fecha, int $vinculo): bool;

    /**
     * Verifica si existe una categoría diferencial activa para un legajo
     *
     * @param int $nroLegajo
     * @param string|array $catDiferencial
     * @return bool
     */
    public function existeCategoriaDiferencial(int $nroLegajo, string|array $catDiferencial): bool;
}
