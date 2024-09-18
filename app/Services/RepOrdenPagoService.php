<?php

namespace App\Services;

use App\Models\Reportes\RepOrdenPagoModel;
use App\Repositories\RepOrdenPagoRepository;

class RepOrdenPagoService
{
    protected $repository;

    /**
     * Crear una nueva instancia
     */
    public function __construct(RepOrdenPagoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all RepOrdenPago records.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRepOrdenPago()
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
