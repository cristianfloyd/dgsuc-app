<?php

namespace App\Services;

use App\Contracts\RetTablaBasicosConcespInterface;
use App\Exceptions\RegistroNoEncontradoException;
use DateTime;

class RetTablaBasicosConcespService
{
    public function __construct(private readonly RetTablaBasicosConcespInterface $repository) {}

    /**
     * Obtiene el monto correspondiente a un registro específico de la tabla de básicos y conceptos.
     *
     * @param DateTime $fecha La fecha para la cual se desea obtener el monto.
     * @param string $catId El ID de la categoría del registro.
     * @param string $concLiqId El ID del concepto de liquidación del registro.
     * @param int $anios Los años de antigüedad del registro.
     *
     * @throws RegistroNoEncontradoException Si no se encuentra un registro que coincida con los criterios dados.
     *
     * @return float El monto correspondiente al registro encontrado.
     */
    public function obtenerMonto(DateTime $fecha, string $catId, string $concLiqId, int $anios): float
    {
        $registro = $this->repository->buscarRegistro($fecha, $catId, $concLiqId, $anios);

        if (!$registro instanceof \App\Models\Suc\RetTablaBasicosConcesp) {
            throw new RegistroNoEncontradoException('No se encontró un registro para los criterios dados.');
        }

        return $registro->monto;
    }
}
