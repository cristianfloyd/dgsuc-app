<?php

namespace App\Repositories\Mapuche;

use App\Contracts\Mapuche\Dh21hRepositoryInterface;
use App\Data\Mapuche\Dh21hData;
use App\Models\Mapuche\Dh21h;
use Illuminate\Support\Facades\Log;

class Dh21hRepository implements Dh21hRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly Dh21h $model)
    {
    }

    /**
     * Obtener liquidación por ID.
     */
    public function findById(int $id): ?Dh21hData
    {
        return $this->model->find($id)?->getData();
    }

    /**
     * Crear nueva liquidación.
     */
    public function create(Dh21hData $data): Dh21h
    {
        return $this->model->create($data->toArray());
    }

    /**
     * @inheritDoc
     */
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->all();
    }

    /**
     * @inheritDoc
     */
    public function delete($id): bool
    {
        try {
            $deletedRows = $this->model->destroy($id);

            return $deletedRows > 0;
        } catch (\Exception $e) {
            Log::error("Error deleting Dh21h with id {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function find($id): Dh21h
    {
        return $this->model->find($id);
    }

    /**
     * @inheritDoc
     */
    public function findByLegajo(int $legajo): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->byLegajo($legajo)->get();
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function update($id, Dh21hData $data): bool
    {
        return $this->model->update($data->toArray(), [$id]);
    }
}
