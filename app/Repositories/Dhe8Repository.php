<?php

namespace App\Repositories;

use App\Models\Dhe8;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Dhe8Repository
 *
 * @package App\Repositories
 */
class Dhe8Repository
{
    /**
     * Obtiene todos los registros
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Dhe8::all();
    }

    /**
     * Busca un registro por su código
     *
     * @param string $codigo
     * @return Dhe8|null
     */
    public function findByCodigo(string $codigo): ?Dhe8
    {
        return Dhe8::find($codigo);
    }

    /**
     * Crea un nuevo registro
     *
     * @param array $data
     * @return Dhe8
     */
    public function create(array $data): Dhe8
    {
        return Dhe8::create($data);
    }

    /**
     * Actualiza un registro existente
     *
     * @param string $codigo
     * @param array $data
     * @return bool
     */
    public function update(string $codigo, array $data): bool
    {
        $dhe8 = Dhe8::find($codigo);
        return $dhe8 ? $dhe8->update($data) : false;
    }

    /**
     * Elimina un registro
     *
     * @param string $codigo
     * @return bool
     */
    public function delete(string $codigo): bool
    {
        $dhe8 = Dhe8::find($codigo);
        return $dhe8 ? $dhe8->delete() : false;
    }
}
