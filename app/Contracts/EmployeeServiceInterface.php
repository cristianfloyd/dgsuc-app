<?php

namespace App\Contracts;

interface EmployeeServiceInterface
{
    /**
     * Busca un empleado por su número de documento.
     *
     * @param string $dni El número de documento del empleado a buscar.
     *
     * @return \App\DTOs\EmployeeInfoDTO|null El objeto DTO con la información del empleado, o null si no se encuentra.
     */
    public function searchEmployee(string $dni): ?\App\DTOs\EmployeeInfoDTO;

    /**
     * Obtiene los cargos asociados a un número de legajo específico.
     *
     * @param string $nroLegaj El número de legajo del empleado.
     *
     * @return array Los cargos asociados al número de legajo.
     */
    public function getCargos(string $nroLegaj): array;

    /**
     * Almacena las líneas procesadas en la base de datos.
     *
     * @param array $processedLines Las líneas procesadas que se van a almacenar.
     *
     * @return bool Verdadero si las líneas se almacenaron correctamente, falso en caso contrario.
     */
    public function storeProcessedLines(array $processedLines): bool;
}
