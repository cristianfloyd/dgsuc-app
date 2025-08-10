<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface CuilRepositoryInterface
{
    public function getCuilsNotInAfip(string $periodoFiscal): Collection;

    public function getCuilsNoEncontrados(): array;
}
