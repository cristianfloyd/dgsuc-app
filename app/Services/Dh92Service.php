<?php

namespace App\Services;

use App\Contracts\Dh92Repository;
use Illuminate\Support\Facades\DB;

class Dh92Service
{
    /**
     * @var Dh92Repository
     */
    protected $repository;

    /**
     * Constructor del servicio.
     *
     * @param Dh92Repository $repository
     */
    public function __construct(Dh92Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crea un nuevo registro con transacción.
     *
     * @param array $data
     *
     * @throws \Exception
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
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un registro con transacción.
     *
     * @param int $id
     * @param array $data
     *
     * @throws \Exception
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
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
