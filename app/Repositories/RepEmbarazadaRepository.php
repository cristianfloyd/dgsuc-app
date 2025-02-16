<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\RepEmbarazada;
use App\Data\RepEmbarazadaData;
use Illuminate\Database\Eloquent\Collection;

class RepEmbarazadaRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly RepEmbarazada $model
    ) {}

    /**
     * Obtener todos los registros.
     *
     * @return Collection<int, RepEmbarazada>
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Encontrar por número de legajo.
     */
    public function findByLegajo(int $nroLegajo): ?RepEmbarazada
    {
        return $this->model->find($nroLegajo);
    }

    /**
     * Crear nuevo registro desde Data Object.
     */
    public function create(RepEmbarazadaData $data): RepEmbarazada
    {
        return $this->model->create($data->toArray());
    }

    /**
     * Actualizar registro desde Data Object.
     */
    public function update(int $nroLegajo, RepEmbarazadaData $data): bool
    {
        return $this->model->where('nro_legaj', $nroLegajo)
            ->update($data->toArray());
    }
}
