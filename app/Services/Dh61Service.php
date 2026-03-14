<?php

namespace App\Services;

use App\Models\Dh11;
use App\Repositories\Dh61RepositoryInterface;

use function count;

class Dh61Service
{
    public function __construct(private readonly Dh61RepositoryInterface $dh61Repository) {}

    /**
     * Restaura una categoría a un estado anterior utilizando un registro histórico.
     *
     * @param Dh11 $category La categoría a restaurar.
     * @param int $year El año del período histórico.
     * @param int $month El mes del período histórico.
     *
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
     *
     * @return array Un array con el estado de la operación y un mensaje informativo.
     *               Ejemplo: ['success' => true, 'message' => 'Categorías restauradas con éxito']
     */
    public function restoreCategoriesByPeriod(int $year, int $month): array
    {
        $historicalRecords = $this->dh61Repository->getRecordsByFiscalPeriod($year, $month);
        if ($historicalRecords->isEmpty()) {
            return [
                'success' => false,
                'message' => "No se encontraron registros para el período {$year}-{$month}",
            ];
        }

        $restoredCount = 0;
        $failedCategories = [];
        foreach ($historicalRecords as $historicalRecord) {
            $category = Dh11::query()->find($historicalRecord->codc_categ);

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
        if ($failedCategories === []) {
            $message = "Se restauraron {$restoredCount} categorías con éxito.";
        } else {
            $message = "Se restauraron {$restoredCount} categorías. ";
            $message .= 'Falló la restauración de las siguientes categorías: ' . implode(', ', $failedCategories);
        }

        return [
            'success' => count($failedCategories) === 0, // Si no hay categorías fallidas, la operación es exitosa.
            'message' => $message,
        ];
    }
}
