<?php

namespace App\Services;

class SubsetSumFinder
{
    /**
     * Encuentra las combinaciones de elementos cuya suma se acerca al valor objetivo
     *
     * @param array $items Array de elementos con sus valores
     * @param float $targetSum Valor objetivo a alcanzar
     * @param float $tolerance Tolerancia permitida (diferencia máxima aceptable)
     * @return array Las combinaciones encontradas que cumplen con el criterio
     */
    public function findCombinations(array $items, float $targetSum, float $tolerance = 0.01): array
    {
        // Ordenamos los elementos por valor para optimizar la búsqueda
        usort($items, function ($a, $b) {
            return $b['importe'] <=> $a['importe'];
        });

        $results = [];
        $this->findSubsets($items, $targetSum, [], 0, 0, $results, $tolerance);

        // Ordenamos los resultados por cercanía al valor objetivo
        usort($results, function ($a, $b) use ($targetSum) {
            $diffA = abs($a['total'] - $targetSum);
            $diffB = abs($b['total'] - $targetSum);
            return $diffA <=> $diffB;
        });

        return $results;
    }

    /**
     * Método recursivo para encontrar subconjuntos que sumen cerca del valor objetivo
     *
     * @param array $items Elementos disponibles
     * @param float $targetSum Valor objetivo
     * @param array $currentSubset Subconjunto actual en evaluación
     * @param int $index Índice actual en el array de elementos
     * @param float $currentSum Suma acumulada del subconjunto actual
     * @param array &$results Resultados encontrados (pasado por referencia)
     * @param float $tolerance Tolerancia permitida
     */
    private function findSubsets(
        array $items,
        float $targetSum,
        array $currentSubset,
        int $index,
        float $currentSum,
        array &$results,
        float $tolerance
    ): void {
        // Si la diferencia está dentro de la tolerancia, guardamos el resultado
        if (abs($currentSum - $targetSum) <= $tolerance) {
            $results[] = [
                'items' => $currentSubset,
                'total' => $currentSum
            ];
        }

        // Si ya procesamos todos los elementos o la suma excede demasiado el objetivo, terminamos
        if ($index >= count($items) || $currentSum > $targetSum * 1.5) {
            return;
        }

        // Optimización: si con todos los elementos restantes no llegamos al objetivo, terminamos
        $remainingSum = 0;
        for ($i = $index; $i < count($items); $i++) {
            $remainingSum += $items[$i]['importe'];
        }

        if ($currentSum + $remainingSum < $targetSum - $tolerance) {
            return;
        }

        // Probamos incluyendo el elemento actual
        $this->findSubsets(
            $items,
            $targetSum,
            array_merge($currentSubset, [$items[$index]]),
            $index + 1,
            $currentSum + $items[$index]['importe'],
            $results,
            $tolerance
        );

        // Probamos sin incluir el elemento actual
        $this->findSubsets(
            $items,
            $targetSum,
            $currentSubset,
            $index + 1,
            $currentSum,
            $results,
            $tolerance
        );
    }
}
