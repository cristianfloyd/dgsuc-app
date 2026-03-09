<?php

namespace App\Contracts;

use App\ValueObjects\NroLiqui;

interface WorkflowExecutionInterface
{
    public function executeWorkflowSteps();

    /**
     * Establece el número de elementos por página.
     */
    public function setPerPage(int $perPage): self;

    /**
     * Obtiene el número actual de elementos por página.
     */
    public function getPerPage(): int;

    /**
     * Establece el período fiscal.
     */
    public function setPeriodoFiscal(int $periodoFiscal): self;

    /**
     * Obtiene el período fiscal actual.
     */
    public function getPeriodoFiscal(): int;

    public function setNroLiqui(int $nroLiqui): self;

    public function getNroLiqui(): NroLiqui;
}
