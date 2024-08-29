<?php

namespace App\Repositories;

use App\Models\Dh11;

interface Dh11RepositoryInterface
{
    public function updateImppBasic(Dh11 $category, float $percentage,array $periodoFiscal): bool;
}
