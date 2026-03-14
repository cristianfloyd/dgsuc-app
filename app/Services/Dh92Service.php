<?php

namespace App\Services;

use App\Repositories\Dh92Repository;
use Exception;
use Illuminate\Support\Facades\DB;

class Dh92Service
{
    /**
     * Constructor del servicio.
     */
    public function __construct(protected Dh92Repository $repository) {}

    /**
     * Crea un nuevo registro con transacción.
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function createWithTransaction(array $data)
    {
        DB::beginTransaction();
        try {
            $result = $this->repository->create($data);
            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un registro con transacción.
     *
     * @param int $id
     *
     * @throws Exception
     *
     * @return bool
     */
    public function updateWithTransaction($id, array $data)
    {
        DB::beginTransaction();
        try {
            $result = $this->repository->update($id, $data);
            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
