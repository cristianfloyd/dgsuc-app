<?php

namespace App\Services;

use App\Models\Dhe8;
use App\Repositories\Dhe8Repository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Dhe8Service
 *
 * @package App\Services
 */
class Dhe8Service
{
    protected $dhe8Repository;

    /**
     * Dhe8Service constructor.
     *
     * @param Dhe8Repository $dhe8Repository
     */
    public function __construct(Dhe8Repository $dhe8Repository)
    {
        $this->dhe8Repository = $dhe8Repository;
    }

    /**
     * Obtiene todos los registros
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->dhe8Repository->getAll();
    }

    /**
     * Busca un registro por su código
     *
     * @param string $codigo
     * @return Dhe8|null
     **/
    public function findByCodigo(string $codigo): ?Dhe8
    {
        return $this->dhe8Repository->findByCodigo($codigo);
    }

    /**
     * Crea un nuevo registro
     *
     * @param array $data
     * @return Dhe8
     */
    public function create(array $data): Dhe8
    {
        // Validación de datos
        $this->validate($data);

        return $this->dhe8Repository->create($data);
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
        // Validación de datos
        $this->validate($data);

        return $this->dhe8Repository->update($codigo, $data);
    }

    /**
     * Elimina un registro
     *
     * @param string $codigo
     * @return bool
     */
    public function delete(string $codigo): bool
    {
        return $this->dhe8Repository->delete($codigo);
    }


    /**
     * Valida los datos proporcionados para crear o actualizar un registro Dhe8.
     *
     * @param array $data Los datos a validar.
     * @return void
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
