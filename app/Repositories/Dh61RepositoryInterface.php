<?php
namespace App\Repositories;

use App\Models\Dh11;
use App\Models\Dh61;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interfaz para el repositorio de Dh61
 *
 * @method void createHistoricalRecord(Dh11 $category)
 * @method Collection getRecordsByFiscalPeriod(int $year, int $month)
 * @method bool exists(string $codc_categ, int $vig_caano, int $vig_cames)
 * @method Collection create(array $attributes)
 * @method Collection getUniqueCategoriesByPeriod(int $year, int $month)
 * @method Collection getCategoryRecordsByPeriod(string $codc_categ, int $year, int $month)
 * @see \App\Models\Dh61
 */
interface Dh61RepositoryInterface
{
    /**
     * Crea un registro histórico basado en una categoría Dh11
     *
     * @param Dh11 $category La categoría para crear el registro histórico
     * @return void
     */
    public function createHistoricalRecord(Dh11 $category): void;

    /**
     * Obtiene los registros por período fiscal
     *
     * @param int $year El año del período fiscal
     * @param int $month El mes del período fiscal
     * @return Collection Los registros encontrados
     */
    public function getRecordsByFiscalPeriod(int $year, int $month): Collection;

    /**
     * Verifica si existe un registro con los parámetros dados
     *
     * @param string $codc_categ El código de categoría
     * @param int $vig_caano El año de vigencia
     * @param int $vig_cames El mes de vigencia
     * @return bool Verdadero si existe, falso en caso contrario
     */
    public function exists(string $codc_categ, int $vig_caano, int $vig_cames): bool;

    /**
     * Crea un nuevo registro Dh61
     *
     * @param array $attributes Los atributos para crear el registro
     * @return Dh61 El registro creado
     */
    public function create(array $attributes): Dh61;

    /**
     * Obtiene las categorías únicas por período
     *
     * @param int $year El año del período
     * @param int $month El mes del período
     * @return Collection Las categorías únicas encontradas
     */
    public function getUniqueCategoriesByPeriod(int $year, int $month): Collection;

    /**
     * Obtiene los registros de categoría por período
     *
     * @param string $codc_categ El código de categoría
     * @param int $year El año del período
     * @param int $month El mes del período
     * @return Collection Los registros de categoría encontrados
     */
    public function getCategoryRecordsByPeriod(string $codc_categ, int $year, int $month): Collection;

    /**
     * Obtiene los registros históricos agrupados por período (año y mes).
     *
     * @return Collection Una colección de colecciones, donde cada colección interna contiene los registros de un período específico.
     */
    public function getRecordsGroupedByPeriod(): Collection;
}
