<?php

namespace App\Services;

use App\Contracts\Dh19RepositoryInterface;
use App\Models\Dh19;
use Illuminate\Database\Eloquent\Collection;

class Dh19Service
{


    /**
     * Constructor del servicio Dh19.
     *
     * @param Dh19RepositoryInterface $repository
     */
    public function __construct(protected Dh19RepositoryInterface $repository)
    {
    }

    /**
     * Obtiene todos los registros de Dh19.
     *
     * @return Collection
     */
    public function getAllDh19(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Obtiene un registro de Dh19 por su clave primaria compuesta.
     *
     * @param int $nroLegaj
     * @param int $codnConce
     * @param string $tipoDocum
     * @param int $nroDocum
     * @return Dh19|null
     */
    public function getDh19ByPrimaryKey(int $nroLegaj, int $codnConce, string $tipoDocum, int $nroDocum): ?Dh19
    {
        return $this->repository->findByPrimaryKey($nroLegaj, $codnConce, $tipoDocum, $nroDocum);
    }

    /**
     * Crea un nuevo registro de Dh19.
     *
     * @param array $data
     * @return Dh19
     */
    public function createDh19(array $data): Dh19
    {
        // Aquí podrías agregar validaciones o lógica de negocio adicional antes de crear
        return $this->repository->create($data);
    }

    /**
     * Actualiza un registro de Dh19.
     *
     * @param Dh19 $dh19
     * @param array $data
     * @return bool
     */
    public function updateDh19(Dh19 $dh19, array $data): bool
    {
        // Aquí podrías agregar validaciones o lógica de negocio adicional antes de actualizar
        return $this->repository->update($dh19, $data);
    }

    /**
     * Elimina un registro de Dh19.
     *
     * @param Dh19 $dh19
     * @return bool
     */
    public function deleteDh19(Dh19 $dh19): bool
    {
        // Aquí podrías agregar lógica adicional antes de eliminar
        return $this->repository->delete($dh19);
    }
}
