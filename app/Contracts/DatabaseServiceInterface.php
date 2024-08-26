<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DatabaseServiceInterface
{
    /**
     * Inserta datos en masa en la base de datos.
     *
     * @param array $datosMapeados Datos mapeados a insertar.
     * @return bool Verdadero si la inserción se realizó correctamente, falso en caso contrario.
     */
    public function insertarDatosMasivos(array $datosMapeados): bool;

    /**
     * Inserta datos en masa en la base de datos.
     *
     * @param array $datosMapeados Datos mapeados a insertar.
     * @return bool Verdadero si la inserción se realizó correctamente, falso en caso contrario.
     */
    public function insertarDatosMasivos2(array $datosMapeados): bool;


    /**
     * Inserta datos en masa en la base de datos.
     *
     * @param Collection $mappedData Datos mapeados a insertar.
     * @param string $tableName Nombre de la tabla donde se insertarán los datos.
     * @return array Resultado de la inserción de datos.
     */
    public function insertBulkData(Collection $mappedData, string $tableName): array;

    public function mapearDatosAlModelo(array $linea): array;
}
