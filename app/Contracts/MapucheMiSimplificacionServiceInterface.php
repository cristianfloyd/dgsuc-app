<?php

namespace App\Contracts;

interface MapucheMiSimplificacionServiceInterface
{
    public function execute(int $nroLiqui,int $periodoFiscal): bool;
}
