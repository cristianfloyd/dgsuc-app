<?php

namespace App\Repositories;

use App\Models\Dhe7;
use Illuminate\Database\Eloquent\Collection;

class Dhe7Repository
{
    // Obtiene todos los registros
    public function getAll(): Collection
    {
        return Dhe7::all();
    }

    // Busca un registro por su código
    public function findByCodigo(string $codigo): ?Dhe7
    {
        return Dhe7::find($codigo);
    }

    // Crea un nuevo registro
    public function create(array $data): Dhe7
    {
        return Dhe7::create($data);
    }

    // Actualiza un registro existente
    public function update(string $codigo, array $data): bool
    {
        $dhe7 = Dhe7::find($codigo);
        return $dhe7 ? $dhe7->update($data) : false;
    }

    // Elimina un registro
    public function delete(string $codigo): bool
    {
        $dhe7 = Dhe7::find($codigo);
        return $dhe7 ? $dhe7->delete() : false;
    }
}
