<?php

namespace App\Contracts\Mapuche;

use App\Data\Mapuche\Dh21hData;
use App\Models\Mapuche\Dh21h;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface Dh21hRepositoryInterface
{
    /**
     * Obtiene todos los registros DH21H.
     *
     * @return mixed
     */
    public function all(): Collection;

    /**
     * Obtiene un registro DH21H por su ID.
     *
     * @param  int  $id
     */
    public function find($id): Dh21h;

    /**
     * Crea un nuevo registro DH21H.
     */
    public function create(Dh21hData $data): Dh21h;

    /**
     * Actualiza un registro DH21H existente.
     *
     * @param  int  $id
     */
    public function update($id, Dh21hData $data): bool;

    /**
     * Elimina un registro DH21H.
     *
     * @param  int  $id
     */
    public function delete($id): bool;

    /**
     * Busca registros Dh21h por criterios específicos.
     *
     * @param  int  $legajo  El legajo del empleado.
     */
    public function findByLegajo(int $legajo): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
