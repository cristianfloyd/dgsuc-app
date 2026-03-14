<?php

namespace App\Repositories;

use App\Models\Dhe8;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Dhe8Repository.
 *
 * @package App\Repositories
 */
class Dhe8Repository
{
    /**
     * Obtiene todos los registros.
     */
    public function getAll(): Collection
    {
        return Dhe8::all();
    }

    /**
     * Busca un registro por su código.
     *
     *
     */
    public function findByCodigo(string $codigo): ?Dhe8
    {
        return Dhe8::query()->find($codigo);
    }

    /**
     * Crea un nuevo registro.
     *
     *
     */
    public function create(array $data): Dhe8
    {
        return Dhe8::query()->create($data);
    }

    /**
     * Actualiza un registro existente.
     *
     *
     */
    public function update(string $codigo, array $data): bool
    {
        $dhe8 = Dhe8::query()->find($codigo);
        return $dhe8 ? $dhe8->update($data) : false;
    }

    /**
     * Elimina un registro.
     *
     *
     */
    public function delete(string $codigo): bool
    {
        $dhe8 = Dhe8::query()->find($codigo);
        return $dhe8 ? $dhe8->delete() : false;
    }
}
