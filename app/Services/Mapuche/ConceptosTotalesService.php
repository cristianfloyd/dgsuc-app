<?php

namespace App\Services\Mapuche;

use App\NroLiqui;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Dh21RepositoryInterface;

class ConceptosTotalesService
{
    protected $repository;

    // Inyección de dependencias para el repositorio
    public function __construct(Dh21RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    public function calcular(NroLiqui $nroLiqui = null, int $codn_fuent = null): Builder
    {
        try {
            // Construcción de la consulta base
            $query = $this->repository->query();

            // Aplicar filtros opcionales
            if ($nroLiqui) {
                $query->where('nro_liqui', '=', $nroLiqui->getValue());
            }
            if ($codn_fuent !== null) {
                $query->where('codn_fuent', '=', $codn_fuent);
            }

            // Aplicar filtros adicionales y agrupación
            $query->where('codn_conce', '>', '100')
                ->whereRaw('codn_conce/100 IN (1,3)')
                ->groupBy('codn_conce')
                ->orderBy('codn_conce');

            // Ejecutar la consulta y retornar los resultados
            return $query;
        } catch (\Exception $e) {
            // Manejo de excepciones
            Log::error('Error en conceptosTotales: ' . $e->getMessage());
            throw $e;
        }
    }}
