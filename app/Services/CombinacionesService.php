<?php

namespace App\Services;

use App\Models\Dh21;
use App\ValueObjects\NroLiqui;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CombinacionesService
{
    /**
     * Encuentra combinaciones de conceptos que sumen aproximadamente un valor objetivo.
     *
     * @param int $nroLegaj Número de legajo
     * @param NroLiqui $nroLiqui Número de liquidación
     * @param float $valorObjetivo Valor a alcanzar (diff_B)
     * @param float $tolerancia Tolerancia permitida
     * @param int $maxCombinaciones Número máximo de combinaciones a devolver
     *
     * @return array Combinaciones encontradas
     */
    public function buscarCombinaciones(
        int $nroLegaj,
        NroLiqui $nroLiqui,
        float $valorObjetivo,
        float $tolerancia = 0.01,
        int $maxCombinaciones = 5,
    ): array {
        try {
            // Obtener conceptos del legajo en la liquidación específica
            $conceptos = $this->obtenerConceptos($nroLegaj, $nroLiqui);

            if ($conceptos->isEmpty()) {
                return [
                    'success' => false,
                    'message' => "No se encontraron conceptos para el legajo {$nroLegaj} en la liquidación {$nroLiqui->value()}",
                    'combinaciones' => [],
                ];
            }

            // Convertir a array para el algoritmo
            $items = $conceptos->map(function ($item) {
                return [
                    'codn_conce' => $item->codn_conce,
                    'impp_conce' => (float)$item->impp_conce,
                    'tipo_conce' => $item->tipo_conce,
                ];
            })->toArray();

            // Buscar combinaciones
            $combinaciones = $this->encontrarCombinaciones($items, $valorObjetivo, $tolerancia);

            // Limitar el número de combinaciones devueltas
            $combinacionesLimitadas = \array_slice($combinaciones, 0, $maxCombinaciones);

            return [
                'success' => true,
                'message' => \count($combinacionesLimitadas) > 0
                    ? 'Se encontraron ' . \count($combinacionesLimitadas) . ' combinaciones posibles'
                    : 'No se encontraron combinaciones que se aproximen al valor objetivo',
                'combinaciones' => $combinacionesLimitadas,
            ];
        } catch (\Exception $e) {
            Log::error('Error al buscar combinaciones: ' . $e->getMessage(), [
                'legajo' => $nroLegaj,
                'nro_liqui' => $nroLiqui->value(),
                'valor_objetivo' => $valorObjetivo,
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar la búsqueda: ' . $e->getMessage(),
                'combinaciones' => [],
            ];
        }
    }

    /**
     * Obtiene los conceptos de un legajo en una liquidación específica.
     *
     * @param int $nroLegaj Número de legajo
     * @param NroLiqui $nroLiqui Número de liquidación
     *
     * @return Collection Colección de conceptos
     */
    private function obtenerConceptos(int $nroLegaj, NroLiqui $nroLiqui): Collection
    {
        return Dh21::where('nro_legaj', $nroLegaj)
            ->where('nro_liqui', $nroLiqui->value())
            ->select('codn_conce', 'impp_conce', 'tipo_conce')
            ->get();
    }

    /**
     * Algoritmo para encontrar combinaciones que sumen aproximadamente un valor objetivo.
     *
     * @param array $items Elementos disponibles
     * @param float $valorObjetivo Valor a alcanzar
     * @param float $tolerancia Tolerancia permitida
     *
     * @return array Combinaciones encontradas
     */
    private function encontrarCombinaciones(array $items, float $valorObjetivo, float $tolerancia): array
    {
        // Optimización: para 50 registros, podemos usar un enfoque más eficiente
        // Separamos los conceptos positivos y negativos para optimizar la búsqueda
        $positivos = array_filter($items, fn ($item) => $item['impp_conce'] > 0);
        $negativos = array_filter($items, fn ($item) => $item['impp_conce'] < 0);

        // Ordenamos de mayor a menor valor absoluto para mejorar la eficiencia
        usort($positivos, fn ($a, $b) => abs($b['impp_conce']) <=> abs($a['impp_conce']));
        usort($negativos, fn ($a, $b) => abs($b['impp_conce']) <=> abs($a['impp_conce']));

        $resultados = [];

        // Usamos un enfoque de búsqueda con poda para mejorar el rendimiento
        $this->buscarSubconjuntos(
            array_merge($positivos, $negativos), // Primero los positivos, luego los negativos
            $valorObjetivo,
            [],
            0,
            0,
            $resultados,
            $tolerancia,
            0,
            5000, // Límite de iteraciones para evitar procesamiento excesivo
        );

        // Ordenamos por cercanía al valor objetivo
        usort($resultados, function ($a, $b) use ($valorObjetivo) {
            $diffA = abs($a['total'] - $valorObjetivo);
            $diffB = abs($b['total'] - $valorObjetivo);
            return $diffA <=> $diffB;
        });

        return $resultados;
    }

    /**
     * Método recursivo optimizado para encontrar subconjuntos.
     *
     * @param array $items Elementos disponibles
     * @param float $valorObjetivo Valor objetivo
     * @param array $subconjuntoActual Subconjunto actual
     * @param int $indice Índice actual
     * @param float $sumaActual Suma acumulada
     * @param array &$resultados Resultados encontrados
     * @param float $tolerancia Tolerancia permitida
     * @param int $profundidad Profundidad de la recursión
     * @param int $maxIteraciones Máximo de iteraciones permitidas
     *
     * @return bool True si debe continuar, false si debe detenerse
     */
    private function buscarSubconjuntos(
        array $items,
        float $valorObjetivo,
        array $subconjuntoActual,
        int $indice,
        float $sumaActual,
        array &$resultados,
        float $tolerancia,
        int $profundidad = 0,
        int $maxIteraciones = 5000,
    ): bool {
        // Control de profundidad para evitar stack overflow
        static $iteraciones = 0;
        $iteraciones++;

        // Limitar el número de iteraciones para evitar procesamiento excesivo
        if ($iteraciones > $maxIteraciones) {
            return false;
        }

        // Si encontramos una combinación dentro de la tolerancia
        if (abs($sumaActual - $valorObjetivo) <= $tolerancia) {
            $resultados[] = [
                'items' => $subconjuntoActual,
                'total' => $sumaActual,
                'diferencia' => abs($sumaActual - $valorObjetivo),
            ];

            // Si ya tenemos suficientes resultados exactos, podemos terminar
            if (\count($resultados) >= 10 && abs($sumaActual - $valorObjetivo) < 0.001) {
                return false;
            }
        }

        // Si ya procesamos todos los elementos o la profundidad es excesiva
        if ($indice >= \count($items) || $profundidad > 10) {
            return true;
        }

        // Optimización: si la diferencia es muy grande, no seguimos por esta rama
        if (abs($sumaActual - $valorObjetivo) > abs($valorObjetivo) * 2) {
            return true;
        }

        // Probamos incluyendo el elemento actual
        $continuar = $this->buscarSubconjuntos(
            $items,
            $valorObjetivo,
            array_merge($subconjuntoActual, [$items[$indice]]),
            $indice + 1,
            $sumaActual + $items[$indice]['impp_conce'],
            $resultados,
            $tolerancia,
            $profundidad + 1,
            $maxIteraciones,
        );

        if (!$continuar) {
            return false;
        }

        // Probamos sin incluir el elemento actual
        return $this->buscarSubconjuntos(
            $items,
            $valorObjetivo,
            $subconjuntoActual,
            $indice + 1,
            $sumaActual,
            $resultados,
            $tolerancia,
            $profundidad + 1,
            $maxIteraciones,
        );
    }
}
