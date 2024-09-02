<?php

namespace App\Repositories;

use App\Models\Suc\RetUda;

class RetUdaRepository
{
    /**
     * Obtiene todos los registros de RetUda.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return RetUda::all();
    }

    /**
     * Obtiene un registro de RetUda por su clave primaria compuesta.
     *
     * @param int $nroLegaj
     * @param int $nroCargo
     * @param string $periodo
     * @return RetUda|null
     */
    public function findByPrimaryKey(int $nroLegaj, int $nroCargo, string $periodo): ?RetUda
    {
        return RetUda::where('nro_legaj', $nroLegaj)
            ->where('nro_cargo', $nroCargo)
            ->where('periodo', $periodo)
            ->first();
    }

    /**
     * Crea un nuevo registro de RetUda.
     *
     * @param array $data
     * @return RetUda
     */
    public function create(array $data): RetUda
    {
        return RetUda::create($data);
    }

    /**
     * Actualiza un registro de RetUda.
     *
     * @param RetUda $retUda
     * @param array $data
     * @return bool
     */
    public function update(RetUda $retUda, array $data): bool
    {
        return $retUda->update($data);
    }

    /**
     * Elimina un registro de RetUda.
     *
     * @param RetUda $retUda
     * @return bool
     */
    public function delete(RetUda $retUda): bool
    {
        return $retUda->delete();
    }
}
