<?php

namespace App\Repositories;

use App\Models\Suc\RetResultado;
use App\ValueObjects\Periodo;
use App\ValueObjects\TipoRetro;

class RetResultadoRepository
{
    /**
     * Obtiene un resultado de retroactivo por legajo, cargo, fecha y periodo.
     *
     * @param int $nroLegaj
     * @param int $nroCargoAnt
     * @param \DateTime $fechaRetDesde
     * @param Periodo $periodo
     *
     * @return RetResultado|null
     */
    public function obtenerPorLlavePrimaria(int $nroLegaj, int $nroCargoAnt, \DateTime $fechaRetDesde, Periodo $periodo): ?RetResultado
    {
        return RetResultado::where('nro_legaj', $nroLegaj)
            ->where('nro_cargo_ant', $nroCargoAnt)
            ->where('fecha_ret_desde', $fechaRetDesde)
            ->where('periodo', $periodo->getValue())
            ->first();
    }

    /**
     * Crea un nuevo resultado de retroactivo.
     *
     * @param array $datos
     *
     * @return RetResultado
     */
    public function crear(array $datos): RetResultado
    {
        return RetResultado::create($datos);
    }

    /**
     * Actualiza un resultado de retroactivo existente.
     *
     * @param RetResultado $retResultado
     * @param array $datos
     *
     * @return bool
     */
    public function actualizar(RetResultado $retResultado, array $datos): bool
    {
        return $retResultado->update($datos);
    }

    /**
     * Elimina un resultado de retroactivo.
     *
     * @param RetResultado $retResultado
     *
     * @return bool|null
     */
    public function eliminar(RetResultado $retResultado): ?bool
    {
        return $retResultado->delete();
    }

    /**
     * Obtiene todos los resultados de retroactivo para un tipo específico.
     *
     * @param TipoRetro $tipoRetro
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerPorTipoRetro(TipoRetro $tipoRetro): \Illuminate\Database\Eloquent\Collection
    {
        return RetResultado::where('tipo_retro', $tipoRetro->getValue())->get();
    }
}
