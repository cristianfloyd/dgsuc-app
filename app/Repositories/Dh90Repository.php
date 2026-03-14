<?php

namespace App\Repositories;

use App\Data\Dh90Data;
use App\Models\Dh90;
use App\Repositories\Interfaces\Dh90RepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;

/**
 * Implementación del repositorio para Dh90.
 */
class Dh90Repository implements Dh90RepositoryInterface
{
    /**
     * Constructor del repositorio.
     */
    public function __construct(protected Dh90 $model) {}

    /**
     * Obtener todos los registros.
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Buscar por número de cargo.
     */
    public function findByNroCargo(int $nroCargo): ?Dh90
    {
        return $this->model->find($nroCargo);
    }

    /**
     * Crear un nuevo registro.
     */
    public function create(Dh90Data $data): Dh90
    {
        try {
            $model = $data->toModel();
            $model->save();

            return $model;
        } catch (Exception $e) {
            throw new Exception('Error al crear el registro: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Actualizar un registro existente.
     */
    public function update(int $nroCargo, Dh90Data $data): ?Dh90
    {
        try {
            $model = $this->findByNroCargo($nroCargo);

            if (!$model instanceof \App\Models\Dh90) {
                return null;
            }

            $data->updateModel($model);
            $model->save();

            return $model;
        } catch (Exception $e) {
            throw new Exception('Error al actualizar el registro: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Eliminar un registro.
     */
    public function delete(int $nroCargo): bool
    {
        try {
            $model = $this->findByNroCargo($nroCargo);

            if (!$model instanceof \App\Models\Dh90) {
                return false;
            }

            return $model->delete();
        } catch (Exception $e) {
            throw new Exception('Error al eliminar el registro: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findByTipoAsociacion(string $tipo): Collection
    {
        return $this->model->where('tipoasociacion', $tipo)->get();
    }
}
