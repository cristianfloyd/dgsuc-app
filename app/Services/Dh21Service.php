<?php

namespace App\Services;

use App\Data\Responses\ConceptoTotalData;
use App\Models\Dh21;
use App\Repositories\Dh21Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\DataCollection;

class Dh21Service
{
    /**
     * Crea una nueva instancia de la clase Dh21Service.
     */
    public function __construct(protected Dh21 $dh21, protected Dh21Repository $dh21Repository)
    {
    }

    /**
     * Obtiene la suma total del concepto 101 en la tabla.
     *
     * @return float
     */
    public function totalConcepto101(?int $nro_liqui = null)
    {
        $query = $this->dh21->where('codn_conce', '101');

        if ($nro_liqui !== null) {
            if ($nro_liqui <= 0) {
                throw new \InvalidArgumentException('El número de liquidación debe ser positivo');
            }
            $query->where('nro_liqui', $nro_liqui);
        }

        return $query->sum('impp_conce');
    }

    /**
     * Obtiene los conceptos totales aplicando filtros opcionales.
     *
     * @param int|null $nro_liqui Número de liquidación (opcional)
     * @param int|null $codn_fuent Código de fuente (opcional)
     *
     * @throws \Exception Si ocurre un error durante la consulta
     *
     * @return Builder Query builder con los conceptos totales
     */
    public function conceptosTotales(?int $nro_liqui = null, ?int $codn_fuent = null): Builder
    {
        try {
            // Construcción de la consulta base
            return $this->dh21->query()
                ->select(
                    DB::raw('ROW_NUMBER() OVER (ORDER BY codn_conce) as id_liquidacion'),
                    'codn_conce',
                    DB::raw('SUM(impp_conce) as total_impp'),
                )
                // Filtro opcional por nro_liqui
                ->when($nro_liqui !== null, function ($query) use ($nro_liqui) {
                    return $query->where('nro_liqui', '=', $nro_liqui);
                })
                // Filtro por codn_conce mayor a 100
                ->where('codn_conce', '>', '100')
                // Filtro opcional por codn_fuent
                ->when($codn_fuent !== null, function ($query) use ($codn_fuent) {
                    return $query->where('codn_fuent', '=', $codn_fuent);
                })
                // Filtro adicional por codn_conce
                ->whereRaw('codn_conce/100 IN (1,3)')
                // Agrupación por codn_conce
                ->groupBy('codn_conce')
                // Ordenación por codn_conce
                ->orderBy('codn_conce');
        } catch (\Exception $e) {
            // Manejo de excepciones
            Log::error('Error en conceptosTotales: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene la colección de conceptos totales como DTOs tipados.
     * Útil para APIs, exportaciones y transformaciones de datos.
     *
     * @param int|null $nro_liqui Número de liquidación (opcional)
     * @param int|null $codn_fuent Código de fuente (opcional)
     *
     * @return DataCollection<ConceptoTotalData>
     */
    public function getConceptosTotalesCollection(?int $nro_liqui = null, ?int $codn_fuent = null): DataCollection
    {
        return new DataCollection(
            ConceptoTotalData::class,
            $this->conceptosTotales($nro_liqui, $codn_fuent)
                ->get()
                ->map(fn ($item) => ConceptoTotalData::fromArray($item->toArray())),
        );
    }

    /**
     * Obtiene las horas y días laborados por un empleado en un cargo específico.
     *
     * @param int $legajo Número de legajo del empleado
     * @param int $cargo Código del cargo
     *
     * @return array Arreglo con las horas y días laborados
     */
    public function obtenerHorasYDias(int $legajo, int $cargo): array
    {
        return $this->dh21Repository->getHorasYDias($legajo, $cargo);
    }

    /**
     * Obtiene las liquidaciones aplicando filtros opcionales.
     *
     * @param array $conditions
     *
     * @return Collection
     */
    public function obtenerLiquidaciones(array $conditions = []): Collection
    {
        return $this->dh21Repository->getLiquidaciones($conditions);
    }
}
