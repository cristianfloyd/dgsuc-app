<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

class CuilCompareService
{
    public function __construct(protected \App\Contracts\CuilRepositoryInterface $cuilRepository) {}

    /**
     * Compara los CUILs y devuelve los que no se encuentran en AFIP.
     *
     * @param string $periodoFiscal Período fiscal en formato YYYYMM.
     * @param int $perPage Número de resultados a devolver por página.
     *
     * @return Collection Una colección de los CUILs que no se encuentran en AFIP.
     */
    public function compareCuils(string $periodoFiscal, int $perPage = 10): Collection
    {
        $data = $this->cuilRepository->getCuilsNotInAfip($periodoFiscal);

        return new Collection($data);
    }

    /**
     * Obtiene un array de CUILs que no se encuentran en AFIP.
     *
     * @return array Un array de CUILs que no se encuentran en AFIP.
     */
    public function getCuilsNoEncontrados(): array
    {
        return $this->cuilRepository->getCuilsNoEncontrados();
    }
}
