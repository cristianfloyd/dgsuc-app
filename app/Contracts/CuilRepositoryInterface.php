<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
interface CuilRepositoryInterface
{
    public function getCuilsNotInAfip(int $perPage = 10): LengthAwarePaginator;
    public function getCuilsNoEncontrados(): array;
}
