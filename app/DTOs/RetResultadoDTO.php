<?php

namespace App\DTOs;

use App\Models\Suc\RetResultado;

class RetResultadoDTO
{
    public int $nroLegaj;

    public int $nroCargoAnt;

    public string $fechaRetDesde;

    public string $periodo;

    public float $montoTotal;

    public function __construct(RetResultado $retResultado, float $montoTotal)
    {
        $this->nroLegaj = $retResultado->nro_legaj;
        $this->nroCargoAnt = $retResultado->nro_cargo_ant;
        $this->fechaRetDesde = $retResultado->fecha_ret_desde->format('Y-m-d');
        $this->periodo = $retResultado->periodo;
        $this->montoTotal = $montoTotal;
    }
}
