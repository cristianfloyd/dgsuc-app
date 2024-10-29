<?php

namespace App\Contracts\Repositories;

use App\Data\DataObjects\Dhr2Data;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface Dhr2RepositoryInterface
{
    public function findByPrimaryKey(int $nro_liqui, int $nro_legaj, int $nro_cargo): ?Dhr2Data;
    public function create(Dhr2Data $data): Dhr2Data;
    public function update(int $nro_liqui, int $nro_legaj, int $nro_cargo, Dhr2Data $data): bool;
    public function delete(int $nro_liqui, int $nro_legaj, int $nro_cargo): bool;
    public function getPaginated(int $perPage = 15): LengthAwarePaginator;
    public function findByLegajo(int $nro_legaj): Collection;
}
