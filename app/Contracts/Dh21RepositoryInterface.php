<?php

namespace App\Contracts;

use App\NroLiqui;
use Illuminate\Database\Eloquent\Builder;

interface Dh21RepositoryInterface
{
    public function query(): Builder;
    public function getDistinctLegajos(): int;
    public function getTotalConcepto101(NroLiqui $nroLiqui = null): float;
}
