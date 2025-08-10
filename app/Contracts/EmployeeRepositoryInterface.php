<?php

namespace App\Contracts;

use App\Models\Dh01;

interface EmployeeRepositoryInterface
{
    public function findByDni(string $dni): ?Dh01;

    public function getFirstEmploymentDate(string $nroLegaj): ?string;

    public function getCargos(string $nroLegaj): array;
}
