<?php

namespace App\Contracts;

interface DatabaseOperationInterface
{
    /**
     * Ejecuta una consulta SQL directa
     *
     * @param string $sql La consulta SQL a ejecutar
     * @param array $bindings Los parámetros para la consulta
     * @return bool Resultado de la operación
     */
    public function executeQuery(string $sql, array $bindings = []): bool;

    /**
     * Elimina una tabla temporal si existe
     *
     * @param string $tableName Nombre de la tabla a eliminar
     * @return bool Resultado de la operación
     */
    public function dropTemporaryTable(string $tableName): bool;

    /**
     * Verifica si una tabla existe
     *
     * @param string $tableName Nombre de la tabla
     * @return bool True si la tabla existe
     */
    public function tableExists(string $tableName): bool;

    /**
     * Ejecuta múltiples consultas en una transacción
     *
     * @param array $queries Array de consultas SQL
     * @return bool Resultado de la operación
     */
    public function executeTransaction(array $queries): bool;
}