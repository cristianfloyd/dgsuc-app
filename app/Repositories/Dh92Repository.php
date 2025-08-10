<?php

namespace App\Repositories;

use App\Models\Dh92;

class Dh92Repository
{
    /**
     * @var Dh92
     */
    protected $model;

    /**
     * Constructor del repositorio.
     *
     * @param Dh92 $model
     */
    public function __construct(Dh92 $model)
    {
        $this->model = $model;
    }

    /**
     * Obtiene todos los registros.
     *
     * @return \Illuminate\Database\Eloquent\Collection
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
     * @param array $data
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
     * @param array $data
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
     *
     * @return bool
     */
    public function delete($id)
    {
        return $this->model->destroy($id) > 0;
    }
}
