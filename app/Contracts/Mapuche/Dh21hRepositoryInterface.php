<?php

namespace App\Contracts\Mapuche;

use App\Models\Mapuche\Dh21h;
use App\Data\Mapuche\Dh21hData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface Dh21hRepositoryInterface
{
    /**
         * Obtiene todos los registros DH21H
         *
         * @return mixed
         */
    public function all(): Collection;

        /**
         * Obtiene un registro DH21H por su ID
         *
         * @param int $id
     * @return Dh21h
         */
        public function find($id): Dh21h;

        /**
         * Crea un nuevo registro DH21H
         *
         * @param Dh21hData $data
         * @return Dh21h
         */
        public function create(Dh21hData $data): Dh21h;

        /**
         * Actualiza un registro DH21H existente
         *
         * @param int $id
         * @param Dh21hData $data
         * @return bool
         */
        public function update($id, Dh21hData $data): bool;

        /**
         * Elimina un registro DH21H
         *
         * @param int $id
         * @return bool
         */
        public function delete($id): bool;

        /**
         * Busca registros DH21H por criterios específicos
         *
         * @param array $criteria
         * @return mixed
         */
    public function findByLegajo(int $legajo): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
