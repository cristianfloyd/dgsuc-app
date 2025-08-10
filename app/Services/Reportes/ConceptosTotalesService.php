<?php

namespace App\Services\Reportes;

use App\Data\Responses\ConceptoTotalAgrupacionData;
use App\Data\Responses\ConceptoTotalItemData;
use App\Repositories\Interfaces\ConceptosTotalesRepositoryInterface;
use Illuminate\Support\Collection;

class ConceptosTotalesService
{
    /**
     * @param ConceptosTotalesRepositoryInterface $repository
     */
    public function __construct(
        private readonly ConceptosTotalesRepositoryInterface $repository,
    ) {
    }

    /**
     * Obtiene los totales por concepto para un período específico.
     *
     * @param array $conceptos Códigos de conceptos a incluir
     * @param int $year Año del período
     * @param int $month Mes del período
     *
     * @return Collection<ConceptoTotalItemData>
     */
    public function getTotalesPorConcepto(array $conceptos, int $year, int $month): Collection
    {
        $resultados = $this->repository->getTotalesPorConcepto($conceptos, $year, $month);

        return $resultados->map(function ($item) {
            return ConceptoTotalItemData::fromRowData($item);
        });
    }

    /**
     * Obtiene los totales por concepto con agrupación por haberes y descuentos.
     *
     * @param array $conceptos Códigos de conceptos a incluir
     * @param int $year Año del período
     * @param int $month Mes del período
     *
     * @return ConceptoTotalAgrupacionData
     */
    public function getTotalesAgrupados(array $conceptos, int $year, int $month): ConceptoTotalAgrupacionData
    {
        $resultados = $this->repository->getTotalesPorConceptoAgrupados($conceptos, $year, $month);

        return ConceptoTotalAgrupacionData::fromRepositoryResult($resultados);
    }

    /**
     * Obtiene un reporte completo de totales por concepto.
     *
     * @param int $year Año del período
     * @param int $month Mes del período
     * @param array|null $conceptos Códigos de conceptos a incluir (opcional)
     *
     * @return ConceptoTotalAgrupacionData
     */
    public function getReporteConceptos(int $year, int $month, ?array $conceptos = null): ConceptoTotalAgrupacionData
    {
        // Si no se especifican conceptos, usamos los predeterminados del reporte
        $conceptosDefault = [
            '201',
            '202',
            '203',
            '204',
            '205',
            '247',
            '248',
            '301',
            '302',
            '303',
            '304',
            '305',
            '306',
            '307',
            '308',
            '347',
            '348',
        ];

        $conceptosAConsultar = $conceptos ?? $conceptosDefault;

        return $this->getTotalesAgrupados($conceptosAConsultar, $year, $month);
    }
}
