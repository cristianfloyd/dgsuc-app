<?php

namespace App\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CuilRepositoryInterface
{
    public function getCuilsNotInAfip(string $periodoFiscal): Collection;
    public function getCuilsNoEncontrados(): array;
}
