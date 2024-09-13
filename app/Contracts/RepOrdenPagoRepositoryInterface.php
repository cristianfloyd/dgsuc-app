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
    public function getAll(): Collection;

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
}
