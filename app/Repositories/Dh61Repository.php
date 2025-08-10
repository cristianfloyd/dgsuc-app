<?php

namespace App\Repositories;

use App\Models\Dh11;
use App\Models\Dh61;
use Illuminate\Database\Eloquent\Collection;

class Dh61Repository implements Dh61RepositoryInterface
{
    /**
     * Crea un nuevo registro histórico en la tabla Dh61 para la categoría especificada.
     *
     * @param Dh11 $category La categoría para la que se creará el registro histórico.
     *
     * @return void
     */
    public function createHistoricalRecord(Dh11 $category): void
    {
        $dh61 = new Dh61();
        $dh61->fill($category->toArray());
        $dh61->save();
    }

    /**
     * Obtiene los registros históricos de la tabla Dh61 para un período fiscal específico.
     *
     * @param int $year El año fiscal.
     * @param int $month El mes fiscal.
     *
     * @return \Illuminate\Database\Eloquent\Collection La colección de registros históricos.
     */
    public function getRecordsByFiscalPeriod(int $year, int $month): Collection
    {
        return Dh61::where('vig_caano', $year)
            ->where('vig_cames', $month)
            ->get();
    }

    public function exists(string $codc_categ, int $vig_caano, int $vig_cames): bool
    {
        return Dh61::where('codc_categ', $codc_categ)
            ->where('vig_caano', $vig_caano)
            ->where('vig_cames', $vig_cames)
            ->exists();
    }

    public function create(array $attributes): Dh61
    {
        return Dh61::create($attributes);
    }

    /**
     * Obtiene las categorías únicas para un período específico.
     *
     * @param int $year El año del período.
     * @param int $month El mes del período.
     *
     * @return Collection Colección de objetos Dh61, cada uno representando una categoría única en el período.
     */
    public function getUniqueCategoriesByPeriod(int $year, int $month): Collection
    {
        return Dh61::where('vig_caano', $year)
            ->where('vig_cames', $month)
            ->distinct('codc_categ')
            ->get();
    }

    /**
     * Obtiene todos los registros históricos de una categoría para un período específico.
     *
     * @param string $categoryId El código de la categoría.
     * @param int $year El año del período.
     * @param int $month El mes del período.
     *
     * @return Collection Colección de objetos Dh61 que coinciden con los criterios.
     */
    public function getCategoryRecordsByPeriod(string $categoryId, int $year, int $month): Collection
    {
        return Dh61::where('codc_categ', $categoryId)
            ->where('vig_caano', $year)
            ->where('vig_cames', $month)
            ->get();
    }

    /**
     * Obtiene los registros históricos agrupados por período (año y mes).
     *
     * @return Collection Una colección de colecciones, donde cada colección interna contiene los registros de un período específico.
     */
    public function getRecordsGroupedByPeriod(): Collection
    {
        return Dh61::all()->groupBy(['vig_caano', 'vig_cames']);
    }
}
