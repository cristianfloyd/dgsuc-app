<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface ConceptosTotalesRepositoryInterface
{
    /**
     * Obtiene los totales por concepto para un período específico.
     *
     * @param array $conceptos Códigos de conceptos a incluir
     * @param int $year Año del período
     * @param int $month Mes del período
     *
     * @return Collection
     */
    public function getTotalesPorConcepto(array $conceptos, int $year, int $month): Collection;

    /**
     * Obtiene los totales por concepto agrupados por tipo (debe/haber).
     *
     * @param array $conceptos Códigos de conceptos a incluir
     * @param int $year Año del período
     * @param int $month Mes del período
     *
     * @return array Resultados agrupados por tipo de concepto
     */
    public function getTotalesPorConceptoAgrupados(array $conceptos, int $year, int $month): array;
}
