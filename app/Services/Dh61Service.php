<?php

namespace App\Services;

use App\Contracts\Dh61RepositoryInterface;
use App\Models\Dh11;

class Dh61Service
{
    private Dh61RepositoryInterface $dh61Repository;

    public function __construct(Dh61RepositoryInterface $dh61Repository)
    {
        $this->dh61Repository = $dh61Repository;
    }

    /**
     * Restaura una categoría a un estado anterior utilizando un registro histórico.
     *
     * @param Dh11 $category La categoría a restaurar.
     * @param int $year El año del período histórico.
     * @param int $month El mes del período histórico.
     * @return bool Verdadero si la restauración fue exitosa, falso en caso contrario.
     */
    public function restoreCategoryFromHistory(Dh11 $category, int $year, int $month): bool
    {
        // Buscar el registro histórico correspondiente.
        $historicalRecord = $this->dh61Repository->getCategoryRecordsByPeriod($category->codc_categ, $year, $month)->first();

        // Si no se encuentra ningún registro histórico, retornar falso.
        if (!$historicalRecord) {
            return false;
        }

        // Restaurar la categoría utilizando el registro histórico.
        return $historicalRecord->restoreCategory($category);
    }

    /**
     * Restaura todas las categorías a un estado anterior utilizando los registros históricos de un período específico.
     *
     * @param int $year El año del período a restaurar.
     * @param int $month El mes del período a restaurar.
     * @return array Un array con el estado de la operación y un mensaje informativo.
     *               Ejemplo: ['success' => true, 'message' => 'Categorías restauradas con éxito']
     */
    public function restoreCategoriesByPeriod(int $year, int $month): array
    {
        // Obtener los registros históricos agrupados por período.
        $groupedRecords = $this->dh61Repository->getRecordsGroupedByPeriod();

        // Verificar si existe el período especificado.
        if (!isset($groupedRecords[$year][$month])) {
            return [
                'success' => false,
                'message' => "No se encontraron registros para el período {$year}-{$month}"
            ];
        }

        // Iterar sobre los registros del período y restaurar las categorías.
        // Iterar sobre los registros del período e intentar restaurar las categorías.
        $restoredCount = 0;
        $failedCategories = [];
        foreach ($groupedRecords[$year][$month] as $historicalRecord) {
            $category = Dh11::find($historicalRecord->codc_categ);

            // Validar si la categoría existe.
            if (!$category) {
                $failedCategories[] = $historicalRecord->codc_categ;
                continue;
            }

            // Intentar restaurar la categoría.
            if ($historicalRecord->restoreCategory($category)) {
                $restoredCount++;
            } else {
                $failedCategories[] = $historicalRecord->codc_categ;
            }
        }

        // Construir el mensaje de respuesta.
        if (empty($failedCategories)) {
            $message = "Se restauraron {$restoredCount} categorías con éxito.";
        } else {
            $message = "Se restauraron {$restoredCount} categorías. ";
            $message .= "Falló la restauración de las siguientes categorías: " . implode(', ', $failedCategories);
        }

        return [
            'success' => count($failedCategories) === 0, // Si no hay categorías fallidas, la operación es exitosa.
            'message' => $message
        ];
    }
}
