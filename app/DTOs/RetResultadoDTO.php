<?php

namespace App\DTOs;

use App\Models\Suc\RetResultado;

class RetResultadoDTO
{
    public int $nroLegaj;

    public int $nroCargoAnt;

    public string $fechaRetDesde;

    public string $periodo;

    public function __construct(RetResultado $retResultado, public float $montoTotal)
    {
        $this->nroLegaj = $retResultado->nro_legaj;
        $this->nroCargoAnt = $retResultado->nro_cargo_ant;
        $this->fechaRetDesde = (string)$retResultado->fecha_ret_desde;
        $this->periodo = $retResultado->periodo;
    }
}
