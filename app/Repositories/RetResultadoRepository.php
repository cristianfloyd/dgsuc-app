<?php

namespace App\Repositories;

use App\Models\Suc\RetResultado;
use App\ValueObjects\Periodo;
use App\ValueObjects\TipoRetro;
use DateTime;
use Illuminate\Database\Eloquent\Collection;

class RetResultadoRepository
{
    /**
     * Obtiene un resultado de retroactivo por legajo, cargo, fecha y periodo.
     */
    public function obtenerPorLlavePrimaria(int $nroLegaj, int $nroCargoAnt, DateTime $fechaRetDesde, Periodo $periodo): ?RetResultado
    {
        return RetResultado::where('nro_legaj', $nroLegaj)
            ->where('nro_cargo_ant', $nroCargoAnt)
            ->where('fecha_ret_desde', $fechaRetDesde)
            ->where('periodo', $periodo->getValue())
            ->first();
    }

    /**
     * Crea un nuevo resultado de retroactivo.
     */
    public function crear(array $datos): RetResultado
    {
        return RetResultado::create($datos);
    }

    /**
     * Actualiza un resultado de retroactivo existente.
     */
    public function actualizar(RetResultado $retResultado, array $datos): bool
    {
        return $retResultado->update($datos);
    }

    /**
     * Elimina un resultado de retroactivo.
     */
    public function eliminar(RetResultado $retResultado): ?bool
    {
        return $retResultado->delete();
    }

    /**
     * Obtiene todos los resultados de retroactivo para un tipo específico.
     *
     * @return Collection<int, RetResultado>
     */
    public function obtenerPorTipoRetro(TipoRetro $tipoRetro): Collection
    {
        return RetResultado::where('tipo_retro', $tipoRetro->getValue())->get();
    }
}
