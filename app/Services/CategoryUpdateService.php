<?php

namespace App\Services;

use App\Models\Dh11;
use App\Repositories\Dh11RepositoryInterface;
use App\Repositories\Dh61RepositoryInterface;
use App\Contracts\CategoryUpdateServiceInterface;


class CategoryUpdateService implements CategoryUpdateServiceInterface
{
    private $dh11Repository;
    private $dh61Repository;

    public function __construct(
        Dh11RepositoryInterface $dh11Repository,
        Dh61RepositoryInterface $dh61Repository
    ){
        $this->dh11Repository = $dh11Repository;
        $this->dh61Repository = $dh61Repository;
    }

    /**
     * Actualiza la categoría con un registro histórico.
     *
     * @param Dh11 $category La categoría a actualizar.
     * @param int $percentage El porcentaje a actualizar en el campo impp_basic de Dh11.
     * @return bool Verdadero si la actualización fue exitosa, falso en caso contrario.
     */
    public function updateCategoryWithHistory(Dh11 $category, float $percentage,array $periodoFiscal = null): bool
    {
        // Primero, crear un registro histórico en Dh61
        $this->dh61Repository->createHistoricalRecord($category);

        // Luego, actualizar el campo impp_basic en Dh11
        return $this->dh11Repository->updateImppBasic($category, $percentage, $periodoFiscal);
    }
}
