<?php

namespace App\Repositories;

use App\Models\Dh92;
use Illuminate\Database\Eloquent\Collection;

class Dh92Repository
{
    /**
     * Constructor del repositorio.
     */
    public function __construct(protected \App\Models\Dh92 $model)
    {
    }

    /**
     * Obtiene todos los registros.
     *
     * @return Collection
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Encuentra un registro por su ID.
     *
     * @param int $id
     *
     * @return Dh92|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Crea un nuevo registro.
     *
     *
     * @return Dh92
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Actualiza un registro existente.
     *
     * @param int $id
     *
     * @return bool
     */
    public function update($id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            return $record->update($data);
        }
        return false;
    }

    /**
     * Elimina un registro.
     *
     * @param int $id
     */
    public function delete($id): bool
    {
        return $this->model->destroy($id) > 0;
    }
}
