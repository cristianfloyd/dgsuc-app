<?php

namespace App\Contracts;

use App\Models\OrigenesModel;

interface OrigenRepositoryInterface
{
    /**
     * Encuentra un modelo de Origenes por su ID.
     *
     * @param int $id El ID del modelo de Origenes a buscar.
     * @return OrigenesModel|null El modelo de Origenes encontrado, o null si no se encontró.
     */
    public function findById(int $id): ?OrigenesModel;
    /**
     * Encuentra un modelo de Origenes por su nombre.
     *
     * @param string $name El nombre del modelo de Origenes a buscar.
     * @return OrigenesModel|null El modelo de Origenes encontrado, o null si no se encontró.
     */
    public function findByName(string $name): ?OrigenesModel;
}
