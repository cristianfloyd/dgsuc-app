<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Data\AfipMapucheSicossCalculoData;
use Illuminate\Pagination\LengthAwarePaginator;

interface AfipMapucheSicossCalculoRepository
{
    public function find(string $cuil): ?AfipMapucheSicossCalculoData;

    public function create(AfipMapucheSicossCalculoData $data): AfipMapucheSicossCalculoData;

    public function update(string $cuil, AfipMapucheSicossCalculoData $data): bool;

    public function delete(string $cuil): bool;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function truncate(): void;
}
