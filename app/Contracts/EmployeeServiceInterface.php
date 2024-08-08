<?php

namespace App\Contracts;

interface EmployeeServiceInterface
{
    public function searchEmployee(string $dni): ?\App\DTOs\EmployeeInfoDTO;
    public function getCargos(string $nroLegaj): array;
    public function storeProcessedLines(array $lineasProcesadas): void;
}
