<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Mapuche\Dhr1;
use App\Data\DataObjects\Dhr1Data;
use Illuminate\Database\Eloquent\Collection;

interface Dhr1RepositoryInterface
{
    public function find(int $nro_liqui): ?Dhr1;
    public function all(): Collection;
    public function create(Dhr1Data $data): Dhr1;
    public function update(int $nro_liqui, Dhr1Data $data): bool;
    public function delete(int $nro_liqui): bool;
}
