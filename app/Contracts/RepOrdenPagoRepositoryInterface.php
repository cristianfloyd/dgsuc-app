<?php

namespace App\Contracts;

use App\Models\Reportes\RepOrdenPagoModel;
use Illuminate\Database\Eloquent\Collection;

interface RepOrdenPagoRepositoryInterface
{
    /**
     * Obtener todos los registros de RepOrdenPago.
     *
     * @return Collection
     */
    public function getAll(array|int|null $nroLiquis = null): Collection;

    public function getAllWithUnidadAcademica(array|int|null $nroLiquis = null): Collection;

    /**
     * Obtener un registro de RepOrdenPago por su número de liquidación.
     *
     * @param int $nroLiqui
     * @return RepOrdenPagoModel|null
     */
    public function getByNroLiqui(int $nroLiqui): ?RepOrdenPagoModel;

    /**
     * Crear un nuevo registro de RepOrdenPago.
     *
     * @param array $data
     * @return RepOrdenPagoModel
     */
    public function create(array $data): RepOrdenPagoModel;

    /**
     * Actualizar un registro de RepOrdenPago.
     *
     * @param RepOrdenPagoModel $repOrdenPago
     * @param array $data
     * @return bool
     */
    public function update(RepOrdenPagoModel $repOrdenPago, array $data): bool;

    /**
     * Eliminar un registro de RepOrdenPago.
     *
     * @param RepOrdenPagoModel $repOrdenPago
     * @return bool
     */
    public function delete(RepOrdenPagoModel $repOrdenPago): bool;

    /**
     * Trunca la tabla rep_orden_pago
     *
     * @return bool
     * @throws \Exception
     */
    public function truncate(): bool;
    
    /**
     * Crea la tabla rep_orden_pago si no existe.
     *
     * @return void
     * @throws \Exception
     */
    public function createTableIfNotExists(): void;
    
    /**
     * Verifica si existe el procedimiento almacenado rep_orden_pago y lo crea si no existe.
     *
     * @return void
     * @throws \Exception
     */
    public function ensureStoredProcedure(): void;
    
    /**
     * Ejecuta el procedimiento almacenado rep_orden_pago con las liquidaciones proporcionadas.
     *
     * @param array $liquidaciones Array de números de liquidación
     * @return void
     * @throws \Exception
     */
    public function executeStoredProcedure(array $liquidaciones): void;
    
    /**
     * Obtiene la definición SQL del procedimiento almacenado rep_orden_pago.
     *
     * @return string
     */
    public function getStoredProcedureDefinition(): string;
}
