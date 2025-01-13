<?php

namespace App\Services\Reportes\Interfaces;

use Illuminate\Support\Collection;
use App\Data\Reportes\BloqueosData;
use App\Models\Reportes\BloqueosDataModel;

interface BloqueosServiceInterface
{
    public function processImport(array $data): Collection;
    public function processRow(array $row): array;
    public function validateData(array $data): bool;
}
