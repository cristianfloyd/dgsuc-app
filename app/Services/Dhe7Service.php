<?php

namespace App\Services;

use App\Contracts\Dhe7Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

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

    protected function validate(array $data)
    {
        $rules = [
            'codigoaccesoescalafon' => 'required|string|size:4',
            'descaccesoescalafon' => 'nullable|string|max:255',
        ];

        Validator::make($data, $rules)->validate();
    }

    // Elimina un registro

    public function update(string $codigo, array $data)
    {
        // Validación de datos
        $this->validate($data);

        return $this->dhe7Repository->update($codigo, $data);
    }

    // Método de validación

    public function delete(string $codigo)
    {
        return $this->dhe7Repository->delete($codigo);
    }
}
