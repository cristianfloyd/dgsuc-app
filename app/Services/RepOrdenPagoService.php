<?php

namespace App\Services;

use App\Repositories\RepOrdenPagoRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Reportes\RepOrdenPagoModel;

/**
 * Proporciona una capa de servicio para administrar registros de RepOrdenPago.
 *
 *
 * Esta clase de servicio proporciona métodos para interactuar con los registros de RepOrdenPago, incluidos:
 *  recuperar todos los registros, recuperar un registro por su nro_liqui, crear un nuevo registro,
 *  actualizar un registro existente y eliminar un registro.
 *
 * La clase de servicio utiliza una clase de repositorio para manejar la lógica de acceso a datos.
 *
 */
class RepOrdenPagoService
{
    protected RepOrdenPagoRepository $repository;

    /**
     * Crear una nueva instancia
     */
    public function __construct(RepOrdenPagoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Obtiene todos los registros de RepOrdenPago.
     *
     * @return Collection
     */
    public function getAllRepOrdenPago(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get RepOrdenPago by nro_liqui.
     *
     * @param int $nroLiqui
     * @return RepOrdenPagoModel|null
     */
    public function getRepOrdenPagoByNroLiqui(int $nroLiqui): ?RepOrdenPagoModel
    {
        return $this->repository->getByNroLiqui($nroLiqui);
    }

    /**
     * Create a new RepOrdenPago record.
     *
     * @param array $data
     * @return RepOrdenPagoModel
     */
    public function createRepOrdenPago(array $data): RepOrdenPagoModel
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing RepOrdenPago record.
     *
     * @param RepOrdenPagoModel $repOrdenPago
     * @param array $data
     * @return bool
     */
    public function updateRepOrdenPago(RepOrdenPagoModel $repOrdenPago, array $data): bool
    {
        return $this->repository->update($repOrdenPago, $data);
    }

    /**
     * Delete a RepOrdenPago record.
     *
     * @param RepOrdenPagoModel $repOrdenPago
     * @return bool
     */
    public function deleteRepOrdenPago(RepOrdenPagoModel $repOrdenPago): bool
    {
        return $this->repository->delete($repOrdenPago);
    }
}
