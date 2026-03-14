<?php

namespace App\Contracts;

use App\Models\Suc\RetTablaBasicosConcesp;
use DateTime;

interface RetTablaBasicosConcespInterface
{
    /**
     * Busca un registro en la tabla de conceptos básicos de retención.
     *
     * @param  DateTime  $fecha  La fecha para la que se busca el registro.
     * @param  string  $catId  El ID de la categoría del concepto.
     * @param  string  $concLiqId  El ID del concepto de liquidación.
     * @param  int  $anios  Los años a considerar.
     * @return RetTablaBasicosConcesp|null El registro encontrado, o null si no se encuentra.
     */
    public function buscarRegistro(DateTime $fecha, string $catId, string $concLiqId, int $anios): ?RetTablaBasicosConcesp;
}
