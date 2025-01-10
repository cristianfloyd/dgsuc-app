<?php

namespace App\Services\Reportes\Interfaces;

interface BloqueosServiceInterface
{
    public function processImport(array $data): array;
    public function processRow(array $row): array;
    public function validateData(array $data): bool;
}
