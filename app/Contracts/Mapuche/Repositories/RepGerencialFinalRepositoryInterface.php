<?php

namespace App\Contracts\Repositories;

interface RepGerencialFinalRepositoryInterface
{
    public function getByLiquidaciones(array $liquidaciones);
    public function getTotalesPorInciso(array $liquidaciones);
    public function getTotalesPorDependencia(array $liquidaciones);
    public function getTotalesPorEscalafon(array $liquidaciones);
    public function getResumenPorAgrupamiento(array $liquidaciones);
}
