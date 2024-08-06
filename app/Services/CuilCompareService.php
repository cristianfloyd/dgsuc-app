<?php

namespace App\Services;

use App\Contracts\CuilRepositoryInterface;
use Illuminate\Support\Collection;

class CuilCompareService
{
    protected $cuilRepository;
    public function __construct(CuilRepositoryInterface $cuilRepository)
    {
        $this->cuilRepository = $cuilRepository;
    }


    /**
     * Compara los CUILs y devuelve los que no se encuentran en AFIP.
     *
     * @param int $perPage Número de resultados a devolver por página.
     * @return \Illuminate\Pagination\LengthAwarePaginator Paginador con los CUILs que no se encuentran en AFIP.
     */
    public function compareCuils($perPage = 10): Collection
    {
        return $this->cuilRepository->getCuilsNotInAfip($perPage);
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
