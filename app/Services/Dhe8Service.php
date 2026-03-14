<?php

namespace App\Services;

use App\Models\Dhe8;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * Class Dhe8Service.
 */
class Dhe8Service
{
    /**
     * Dhe8Service constructor.
     */
    public function __construct(protected \App\Repositories\Dhe8Repository $dhe8Repository) {}

    /**
     * Obtiene todos los registros.
     */
    public function getAll(): Collection
    {
        return $this->dhe8Repository->getAll();
    }

    /**
     * Busca un registro por su código.
     *
     *
     **/
    public function findByCodigo(string $codigo): ?Dhe8
    {
        return $this->dhe8Repository->findByCodigo($codigo);
    }

    /**
     * Crea un nuevo registro.
     */
    public function create(array $data): Dhe8
    {
        // Validación de datos
        $this->validate($data);

        return $this->dhe8Repository->create($data);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(string $codigo, array $data): bool
    {
        // Validación de datos
        $this->validate($data);

        return $this->dhe8Repository->update($codigo, $data);
    }

    /**
     * Elimina un registro.
     */
    public function delete(string $codigo): bool
    {
        return $this->dhe8Repository->delete($codigo);
    }

    /**
     * Valida los datos proporcionados para crear o actualizar un registro Dhe8.
     *
     * @param array $data Los datos a validar.
     */
    protected function validate(array $data): void
    {
        $rules = [
            'codigogradooa' => 'required|string|size:4',
            'descgradooa' => 'nullable|string|max:255',
        ];

        Validator::make($data, $rules)->validate();
    }
}
