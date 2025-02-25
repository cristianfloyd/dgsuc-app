<?php

namespace App\Repositories;

use App\Models\Dh90;
use App\Data\Dh90Data;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\Dh90RepositoryInterface;

/**
 * Implementación del repositorio para Dh90.
 */
class Dh90Repository implements Dh90RepositoryInterface
{
    /**
     * Constructor del repositorio.
     *
     * @param Dh90 $model
     */
    public function __construct(protected Dh90 $model)
    {
    }

    /**
     * Obtener todos los registros.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Buscar por número de cargo.
     *
     * @param int $nroCargo
     * @return Dh90|null
     */
    public function findByNroCargo(int $nroCargo): ?Dh90
    {
        return $this->model->find($nroCargo);
    }

    /**
     * Crear un nuevo registro.
     *
     * @param Dh90Data $data
     * @return Dh90
     */
    public function create(Dh90Data $data): Dh90
    {
        try {
            $model = $data->toModel();
            $model->save();
            return $model;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear el registro: " . $e->getMessage());
        }
    }

    /**
     * Actualizar un registro existente.
     *
     * @param int $nroCargo
     * @param Dh90Data $data
     * @return Dh90|null
     */
    public function update(int $nroCargo, Dh90Data $data): ?Dh90
    {
        try {
            $model = $this->findByNroCargo($nroCargo);

            if (!$model) {
                return null;
            }

            $data->updateModel($model);
            $model->save();

            return $model;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar el registro: " . $e->getMessage());
        }
    }

    /**
     * Eliminar un registro.
     *
     * @param int $nroCargo
     * @return bool
     */
    public function delete(int $nroCargo): bool
    {
        try {
            $model = $this->findByNroCargo($nroCargo);

            if (!$model) {
                return false;
            }

            return $model->delete();
        } catch (\Exception $e) {
            throw new \Exception("Error al eliminar el registro: " . $e->getMessage());
        }
    }
    /**
     * @inheritDoc
     */
    public function findByTipoAsociacion(string $tipo): Collection
    {
        return $this->model->where('tipoasociacion', $tipo)->get();
    }
}
