<?php

namespace App\Contracts;

/**
 * Interfaz que define los métodos para manejar transacciones.
 *
 * @method mixed executeInTransaction()
 */
interface TransactionServiceInterface
{
    /**
     * Ejecuta una operación dentro de una transacción.
     *
     * @param callable $callback Función a ejecutar dentro de la transacción.
     *
     * @return mixed Resultado de la ejecución de la función.
     */
    public function executeInTransaction(callable $callback);
}
