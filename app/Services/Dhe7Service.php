<?php

namespace App\Services;

use App\Repositories\Dhe7Repository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;

class Dhe7Service
{
    protected $dhe7Repository;

    public function __construct(Dhe7Repository $dhe7Repository)
    {
        $this->dhe7Repository = $dhe7Repository;
    }

    // Obtiene todos los registros
    public function getAll(): Collection
    {
        return $this->dhe7Repository->getAll();
    }

    // Busca un registro por su código
    public function findByCodigo(string $codigo)
    {
        return $this->dhe7Repository->findByCodigo($codigo);
    }

    // Crea un nuevo registro
    public function create(array $data)
    {
        // Validación de datos
        $this->validate($data);

        return $this->dhe7Repository->create($data);
    }

    // Actualiza un registro existente
    public function update(string $codigo, array $data)
    {
        // Validación de datos
        $this->validate($data);

        return $this->dhe7Repository->update($codigo, $data);
    }

    // Elimina un registro
    public function delete(string $codigo)
    {
        return $this->dhe7Repository->delete($codigo);
    }

    // Método de validación
    protected function validate(array $data)
    {
        $rules = [
            'codigoaccesoescalafon' => 'required|string|size:4',
            'descaccesoescalafon' => 'nullable|string|max:255',
        ];

        Validator::make($data, $rules)->validate();
    }
}
