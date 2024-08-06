<?php

namespace App\Contracts;

interface MapucheMiSimplificacionServiceInterface
{
    public function execute($nroLiqui, $periodoFiscal): bool;
}
