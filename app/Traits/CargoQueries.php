<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;

/**
 * Trait que contiene consultas complejas relacionadas con cargos.
 */
trait CargoQueries
{
    /**
     * Obtiene cargos con relaciones específicas.
     *
     * @param int|null $tipoAsociacion
     *
     * @return Collection
     */
    public static function obtenerCargosRelacionados(?string $tipoAsociacion = null)
    {
        $query = static::query()->conCargosAsociados();

        if ($tipoAsociacion) {
            $query->porTipoAsociacion($tipoAsociacion);
        }

        return $query->get();
    }

    /**
     * Encuentra relaciones entre cargos basadas en criterios específicos.
     *
     *
     * @return Collection
     */
    public static function encontrarRelacionesPorCargo(int $nroCargo)
    {
        return static::query()
            ->where('nro_cargo', $nroCargo)
            ->orWhere('nro_cargoasociado', $nroCargo)
            ->get();
    }
}
