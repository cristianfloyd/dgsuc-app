<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DatabaseServiceInterface
{
    public function insertarDatosMasivos(array $datosMapeados): bool;

    public function insertarDatosMasivos2(array $datosMapeados): bool;

    public function insertBulkData(Collection $mappedData, string $tableName): int;

    public function mapearDatosAlModelo(array $linea): array;
}
