<?php

namespace App\Contracts;

use App\Models\Dh11;

interface CategoryUpdateServiceInterface
{
    public function updateCategoryWithHistory(Dh11 $category, float $percentage, array $periodoFiscal): bool;
}
