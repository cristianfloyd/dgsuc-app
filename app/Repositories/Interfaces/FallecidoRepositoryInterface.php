<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Data\Reportes\FallecidoData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FallecidoRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findByLegajo(int $nroLegajo): ?FallecidoData;

    public function create(FallecidoData $data): FallecidoData;

    public function update(int $nroLegajo, FallecidoData $data): ?FallecidoData;

    public function delete(int $nroLegajo): bool;
}
