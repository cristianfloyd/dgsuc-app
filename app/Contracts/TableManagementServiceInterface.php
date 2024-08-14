<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface TableManagementServiceInterface
{
    /**
     * Verifica y prepara una tabla de base de datos.
     *
     * @param string $tableName Nombre de la tabla a verificar y preparar.
     * @param string|null $connection Nombre de la conexión de base de datos a utilizar.
     * @return void
     */
    public static function verifyAndPrepareTable(string $tableName, string $connection = null): bool;

    /**
     * Verifica si una tabla de base de datos está vacía.
     *
     * @param \Illuminate\Database\Eloquent\Model $model Modelo Eloquent asociado a la tabla.
     * @param string $tableName Nombre de la tabla a verificar.
     * @return bool Verdadero si la tabla está vacía, falso en caso contrario.
     */
    public static function verifyTableIsEmpty(Model $model, string $tableName): bool;
    // public static function verifyAndPrepareTable(string $tableName, string $connection = null): void;
    // public static function verifyTableIsEmpty(Model $model, string $tableName): bool;
}
