<?php

namespace App\Repositories;

use App\Models\Suc\RetUda;
use Illuminate\Database\Eloquent\Collection;

class RetUdaRepository
{
    /**
     * Obtiene todos los registros de RetUda.
     *
     * @return Collection
     */
    public function getAll()
    {
        return RetUda::all();
    }

    /**
     * Obtiene un registro de RetUda por su clave primaria compuesta.
     *
     *
     */
    public function findByPrimaryKey(int $nroLegaj, int $nroCargo, string $periodo): ?RetUda
    {
        return RetUda::query()->where('nro_legaj', $nroLegaj)
            ->where('nro_cargo', $nroCargo)
            ->where('periodo', $periodo)
            ->first();
    }

    /**
     * Crea un nuevo registro de RetUda.
     *
     *
     */
    public function create(array $data): RetUda
    {
        return RetUda::query()->create($data);
    }

    /**
     * Actualiza un registro de RetUda.
     *
     *
     */
    public function update(RetUda $retUda, array $data): bool
    {
        return $retUda->update($data);
    }

    /**
     * Elimina un registro de RetUda.
     *
     *
     */
    public function delete(RetUda $retUda): bool
    {
        return $retUda->delete();
    }
}
