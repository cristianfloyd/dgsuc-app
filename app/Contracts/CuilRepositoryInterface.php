<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CuilRepositoryInterface
{
    public function getCuilsNotInAfip(int $perPage = 10): Collection;
    public function getCuilsNoEncontrados(): array;
}
