<?php

namespace App\Services;

use App\Contracts\TransactionServiceInterface;
use Illuminate\Support\Facades\DB;

class TransactionService implements TransactionServiceInterface
{
    /**
     * Ejecuta un callback dentro de una transacción de base de datos.
     *
     * @param callable $callback El callback a ejecutar dentro de la transacción.
     *
     * @return mixed El resultado del callback.
     */
    public function executeInTransaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
