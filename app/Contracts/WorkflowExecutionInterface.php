<?php

namespace App\Contracts;

use App\ValueObjects\NroLiqui;

interface WorkflowExecutionInterface
{
    public function executeWorkflowSteps();

    /**
     * Establece el número de elementos por página.
     *
     * @param int $perPage
     *
     * @return self
     */
    public function setPerPage(int $perPage): self;

    /**
     * Obtiene el número actual de elementos por página.
     *
     * @return int
     */
    public function getPerPage(): int;

    /**
     * Establece el período fiscal.
     *
     * @param int $periodoFiscal
     *
     * @return self
     */
    public function setPeriodoFiscal(int $periodoFiscal): self;

    /**
     * Obtiene el período fiscal actual.
     *
     * @return int
     */
    public function getPeriodoFiscal(): int;

    public function setNroLiqui(int $nroLiqui): self;

    public function getNroLiqui(): NroLiqui;
}
