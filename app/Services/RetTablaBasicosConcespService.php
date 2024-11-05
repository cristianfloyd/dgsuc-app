<?php

namespace App\Services;

use App\Contracts\RetTablaBasicosConcespRepository;
use App\Exceptions\RegistroNoEncontradoException;

class RetTablaBasicosConcespService
{
    private $repository;

    public function __construct(RetTablaBasicosConcespRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Obtiene el monto correspondiente a un registro específico de la tabla de básicos y conceptos.
     *
     * @param \DateTime $fecha La fecha para la cual se desea obtener el monto.
     * @param string $catId El ID de la categoría del registro.
     * @param string $concLiqId El ID del concepto de liquidación del registro.
     * @param int $anios Los años de antigüedad del registro.
     * @return float El monto correspondiente al registro encontrado.
     * @throws RegistroNoEncontradoException Si no se encuentra un registro que coincida con los criterios dados.
     */
    public function obtenerMonto(\DateTime $fecha, string $catId, string $concLiqId, int $anios): float
    {
        $registro = $this->repository->buscarRegistro($fecha, $catId, $concLiqId, $anios);

        if (!$registro) {
            throw new RegistroNoEncontradoException("No se encontró un registro para los criterios dados.");
        }

        return $registro->monto;
    }
}
