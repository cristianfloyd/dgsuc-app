<?php

namespace App\Contracts;

use App\Models\Reportes\RepOrdenPagoModel;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface RepOrdenPagoRepositoryInterface
{
    /**
     * Obtener todos los registros de RepOrdenPago.
     */
    public function getAll(array|int|null $nroLiquis = null): Collection;

    public function getAllWithUnidadAcademica(array|int|null $nroLiquis = null): Collection;

    /**
     * Obtener un registro de RepOrdenPago por su número de liquidación.
     */
    public function getByNroLiqui(int $nroLiqui): ?RepOrdenPagoModel;

    /**
     * Crear un nuevo registro de RepOrdenPago.
     */
    public function create(array $data): RepOrdenPagoModel;

    /**
     * Actualizar un registro de RepOrdenPago.
     */
    public function update(RepOrdenPagoModel $repOrdenPago, array $data): bool;

    /**
     * Eliminar un registro de RepOrdenPago.
     */
    public function delete(RepOrdenPagoModel $repOrdenPago): bool;

    /**
     * Trunca la tabla rep_orden_pago.
     *
     * @throws Exception
     */
    public function truncate(): bool;

    /**
     * Crea la tabla rep_orden_pago si no existe.
     *
     * @throws Exception
     */
    public function createTableIfNotExists(): void;

    /**
     * Verifica si existe el procedimiento almacenado rep_orden_pago y lo crea si no existe.
     *
     * @throws Exception
     */
    public function ensureStoredProcedure(): void;

    /**
     * Ejecuta el procedimiento almacenado rep_orden_pago con las liquidaciones proporcionadas.
     *
     * @param  array  $liquidaciones  Array de números de liquidación
     *
     * @throws Exception
     */
    public function executeStoredProcedure(array $liquidaciones): void;

    /**
     * Obtiene la definición SQL del procedimiento almacenado rep_orden_pago.
     */
    public function getStoredProcedureDefinition(): string;
}
